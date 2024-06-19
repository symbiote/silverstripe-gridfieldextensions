<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_SaveHandler;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use Exception;

/**
 * Builds on the {@link GridFieldEditableColumns} component to allow creating new records.
 */
class GridFieldAddNewInlineButton extends AbstractGridFieldComponent implements
    GridField_HTMLProvider,
    GridField_SaveHandler
{
    const POST_KEY = 'GridFieldAddNewInlineButton';

    private $fragment;

    private $title;

    /**
     * @param string $fragment the fragment to render the button in
     */
    public function __construct($fragment = 'buttons-before-left')
    {
        $this->setFragment($fragment);
        $this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));
    }

    /**
     * Gets the fragment name this button is rendered into.
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Sets the fragment name this button is rendered into.
     *
     * @param string $fragment
     * @return GridFieldAddNewInlineButton $this
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * Gets the button title text.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the button title text.
     *
     * @param string $title
     * @return GridFieldAddNewInlineButton $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getHTMLFragments($grid)
    {
        if ($grid->getList() && !singleton($grid->getModelClass())->canCreate()) {
            return array();
        }

        $fragment = $this->getFragment();

        /** @var GridFieldEditableColumns $editable */
        $editable = $grid->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        if (!$editable) {
            throw new Exception('Inline adding requires the editable columns component');
        }

        Requirements::javascript('symbiote/silverstripe-gridfieldextensions:javascript/tmpl.js');
        GridFieldExtensions::include_requirements();

        $data = ArrayData::create(array(
            'Title'  => $this->getTitle(),
        ));

        return array(
            $fragment => $data->renderWith(__CLASS__),
            'after'   => $this->getRowTemplate($grid, $editable)
        );
    }

    private function getRowTemplate(GridField $grid, GridFieldEditableColumns $editable)
    {
        $columns = ArrayList::create();
        $handled = array_keys($editable->getDisplayFields($grid) ?? []);

        if ($grid->getList()) {
            $record = Injector::inst()->create($grid->getModelClass());
        } else {
            $record = null;
        }

        $fields = $editable->getFields($grid, $record);

        foreach ($grid->getColumns() as $column) {
            if (in_array($column, $handled ?? [])) {
                $field = $fields->dataFieldByName($column);
                $field->setName(sprintf(
                    '%s[%s][{%%=o.num%%}][%s]',
                    $grid->getName(),
                    GridFieldAddNewInlineButton::POST_KEY,
                    $field->getName()
                ));

                if ($record && $record->hasField($column)) {
                    $field->setValue($record->getField($column));
                }
                $content = $field->Field();
            } else {
                $content = $grid->getColumnContent($record, $column);

                // Convert GridFieldEditableColumns to the template format
                $content = str_replace(
                    sprintf('[%s][0]', GridFieldEditableColumns::POST_KEY),
                    sprintf('[%s][{%%=o.num%%}]', GridFieldAddNewInlineButton::POST_KEY),
                    $content ?? ''
                );
            }

            // Cast content
            if (! $content instanceof DBField) {
                $content = DBField::create_field('HTMLFragment', $content);
            }

            $attrs = '';

            foreach ($grid->getColumnAttributes($record, $column) as $attr => $val) {
                $attrs .= sprintf(' %s="%s"', $attr, Convert::raw2att($val));
            }

            $columns->push(ArrayData::create(array(
                'Content'    => $content,
                'Attributes' => DBField::create_field('HTMLFragment', $attrs),
                'IsActions'  => $column == 'Actions'
            )));
        }

        return $columns->renderWith('Symbiote\\GridFieldExtensions\\GridFieldAddNewInlineRow');
    }

    public function handleSave(GridField $grid, DataObjectInterface $record)
    {
        $list  = $grid->getList();
        $value = $grid->Value();

        if (!isset($value[GridFieldAddNewInlineButton::POST_KEY])
            || !is_array($value[GridFieldAddNewInlineButton::POST_KEY])
        ) {
            return;
        }

        $class    = $grid->getModelClass();
        /** @var GridFieldEditableColumns $editable */
        $editable = $grid->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        /** @var GridFieldOrderableRows $sortable */
        $sortable = $grid->getConfig()->getComponentByType(GridFieldOrderableRows::class);

        if (!singleton($class)->canCreate()) {
            return;
        }

        foreach ($value[GridFieldAddNewInlineButton::POST_KEY] as $fields) {
            /** @var DataObject $item */
            $item  = $class::create();

            // Add the item before the form is loaded so that the join-object is available
            if ($list instanceof ManyManyThroughList) {
                $list->add($item);
            }

            $extra = array();

            $form = $editable->getForm($grid, $item);
            $form->loadDataFrom($fields, Form::MERGE_CLEAR_MISSING);
            $form->saveInto($item);

            // Check if we are also sorting these records
            if ($sortable) {
                $sortField = $sortable->getSortField();
                $item->setField($sortField, $fields[$sortField]);
            }

            if ($list instanceof ManyManyList) {
                $extra = array_intersect_key($form->getData() ?? [], (array) $list->getExtraFields());
            }

            $item->write(false, false, false, true);

            // Add non-through lists after the write. many_many_extraFields are added there too
            if (!($list instanceof ManyManyThroughList)) {
                $list->add($item, $extra);
            }
        }
    }
}
