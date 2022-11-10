<?php

namespace Symbiote\GridFieldExtensions\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest as CoreGridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\HTML;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\GridFieldExtensions;

/**
 * @property CoreGridFieldDetailForm_ItemRequest $owner
 */
class GridFieldDetailFormItemRequestExtension extends Extension
{
    /**
     * @param FieldList $actions
     */
    public function updateFormActions(FieldList &$actions)
    {
        $grid = $this->owner->getGridField();
        $gridFieldConfig = $grid->getConfig();
        $addMultiClassComponent = $gridFieldConfig->getComponentByType(GridFieldAddNewMultiClass::class);
        if ($addMultiClassComponent) {
            $newRecordField = static::get_new_record_field_from_actions($actions);
            if ($newRecordField) {
                $newRecordField->getContainerFieldList()->removeByName('new-record');
                $newRecordField->getContainerFieldList()->push(
                    LiteralField::create('new-record', $this->getHTMLFragment($addMultiClassComponent))
                );
                GridFieldExtensions::include_requirements();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    private function getHTMLFragment(GridFieldAddNewMultiClass $component)
    {
        $grid = $this->owner->getGridField();

        $classes = $component->getClasses($grid);

        if (!count($classes ?? [])) {
            return false;
        }

        return HTML::createTag('a', [
            'data-href-template' => Controller::join_links($grid->Link(), 'add-multi-class', '{class}'),
            'title' => _t(__CLASS__ . '.NEW', 'Add new record'),
            'aria-label' => _t(__CLASS__ . '.NEW', 'Add new record'),
            'class' => implode(' ', array(
                'btn',
                'btn-primary',
                'font-icon-plus-thin',
                'btn--circular',
                'action--new',
                'discard-confirmation',
                'action--new__multi-class',
            )),
            'data-classes' => json_encode($classes),
        ]);
    }

    /**
     * @param FieldList $actions
     * @return LiteralField OR NULL
     */
    private static function get_new_record_field_from_actions(FieldList &$actions)
    {
        $rightGroup = $actions->fieldByName('RightGroup');
        if (!$rightGroup) {
            return null;
        }
        return $rightGroup->getChildren()->fieldByName('new-record');
    }
}
