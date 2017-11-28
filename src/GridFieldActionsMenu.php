<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Silverstripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * Provides an encapsulated list of actions to do with the current record
 * such as edit, publish, unpublish, archive, et al.
 */
class GridFieldActionsMenu implements
    GridField_ColumnProvider,
    GridField_URLHandler
{
    /**
     * Whether to include the first Root tab in the actions list.
     * The first entry generally relates to the default action, which often
     * is also enacted by simply clicking on the row itself - so may be hidden
     * in this case, or shown if clicking the row performs no action.
     *
     * @var boolean
     */
    protected $showFirstTab = true;

    /**
     * The action items to show in the menu
     *
     * Example structure:
     *
     * <code>
     * // Actions
     * [
     *     // Group
     *     [
     *         // Action
     *         [
     *             'Title' => 'Content',
     *             'Link' => 'hrefme',
     *             'Type' => 'arbitrary_name', // e.g. 'link' or 'versioning'
     *         ],
     *         ...
     *     ],
     *     // or a callable that generates the above Group level array format
     *     function($gridField, $record, $typeName) ...
     *     ...
     * ]
     * </code>
     *
     * @var array
     */
    protected $actions = [];

    public function __construct($showFirstTab = true)
    {
        $this->setShowFirstTab($showFirstTab);
        $this->actions = [
            'link' => [$this, 'addRootTabActions'],
            'versioning' => [$this, 'addVersionedActions']
        ];
    }

    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    /**
     * Add each of the "Root" tabs to the actions for this component
     * We expect that a tabbed list of fields will always have a singular root.
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $typeName
     *
     * @return array
     */
    protected function addRootTabActions(GridField $gridField, DataObject $record, $typeName)
    {
        $tabSet = $record->getCMSFields()->first();
        if (!($tabSet instanceof TabSet)) {
            return [];
        }

        $first = true;
        $actions = [];
        foreach ($tabSet->Tabs() as $tab) {
            /** @var \SilverStripe\Forms\Tab $tab */
            $tabID = ($first) ? null : $tab->ID();
            $link = Controller::join_links($gridField->Link('item'), $record->ID, 'edit');
            // @TODO hack workaround: `&& false` here because some JS in the CMS is rewriting
            // a link with a hash in it to the page we're on _now_ #anchor, as opposed to
            // e.g link/set/here#anchor
            $link = $tabID && false ? "$link#$tabID" : $link;

            $actions[] = [
                'Title' => $tab->Title(),
                'Link' => $link,
                'Type' => $typeName
            ];
            $first = false;
        }
        return $actions;
    }

    /**
     * If the object is versioned (has the {@link Versioned} extension applied) then add
     * actions to publish, unpublish, and archive - depending on record state.
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $typeName
     *
     * @return array
     */
    protected function addVersionedActions(GridField $gridField, DataObject $record, $typeName)
    {
        if (!$record->hasExtension(Versioned::class)) {
            return [];
        }

        $actions = [];

        if (!$record->latestPublished()) {
            $actions[] = [
                'Title' => _t(__CLASS__ . '.Publish', 'Publish'),
                'Link' => Controller::join_links($gridField->Link('item'), $record->ID, 'publish'),
                'Type' => $typeName
            ];
        }

        if ($record->isPublished()) {
            $actions[] = [
                'Title' => _t(__CLASS__ . '.Unpublish', 'Unpublish'),
                'Link' => Controller::join_links($gridField->Link('item'), $record->ID, 'unpublish'),
                'Type' => $typeName
            ];
        }

        $actions[] = [
            'Title' => _t(__CLASS__ . '.Delete', 'Delete'),
            'Link' => Controller::join_links($gridField->Link('item'), $record->ID, 'archive'),
            'Type' => $typeName
        ];

        return $actions;
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        $actionBuilder = function ($item, $section) use ($gridField, $record) {
            $type = is_string($section) ? $section : null;
            $result = (is_callable($item)) ?
                $item($gridField, $record, $type) :
                $item;
            return $result;
        };
        // compile our action lists
        $actions = $this->getActions();
        $actions = array_map($actionBuilder, $actions, array_keys($actions));
        if (!$this->getShowFirstTab()) {
            reset($actions);
            $first = key($actions);
            array_shift($actions[$first]);
        }
        // remove empty entries
        foreach ($actions as $section => $sectionItems) {
            $actions[$section] = array_filter($sectionItems);
        }
        $actions = array_filter($actions);
        // render
        GridFieldExtensions::include_requirements();
        $templateData = ArrayData::create([
            'Actions' => Convert::raw2json($actions),
        ]);
        $template = SSViewer::get_templates_by_class($this, '', static::class);

        return $templateData->renderWith($template);
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return [
            'class' => 'grid-field__col-compact actions-menu',
        ];
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName === 'Actions') {
            return [
                'title' => _t(__CLASS__ . '.MoreActions', 'More Actions'),
            ];
        }
        return [];
    }

    public function getURLHandlers($gridField)
    {
        return [
            'item//$ID/$Action' => 'handleRecordAction',
        ];
    }

    /**
     * Handle actions that don't require loading of a new page/panel/etc.
     *
     * @param GridField $gridField
     * @param HTTPRequest $request
     */
    public function handleRecordAction($gridField, $request)
    {
        $record = $gridField->getList()->byID($request->param("ID"));
        return GridFieldRecordActionHandler::create($gridField, $record)->handleRequest($request);
    }

    /**
     * Set whether to include the first Root tab in the actions list
     *
     * @param bool $showFirstTab
     * @return $this
     */
    public function setShowFirstTab($showFirstTab)
    {
        $this->showFirstTab = (bool)$showFirstTab;
        return $this;
    }

    /**
     * Get whether to include the first Root tab in the actions list
     *
     * @return bool
     */
    public function getShowFirstTab()
    {
        return $this->showFirstTab;
    }

    /**
     * Set the actions to use in the dropdown menu
     *
     * @param array $actions
     * @return $this
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * Get the actions to use in the dropdown menu
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
}
