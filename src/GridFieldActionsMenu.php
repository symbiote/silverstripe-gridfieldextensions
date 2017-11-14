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
use SilverStripe\Forms\Tab;
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
     * Whether to include the first Root tab in the actions list
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
     *             'Type' => 'link', // or 'versioning'
     *         ],
     *         ...
     *     ],
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
     * Construct a list of dropdown menu actions to provide for the menu. This includes a list
     * of the Root level tabs from the given record's FieldList, and some Versioned actions
     * (publish, unpublish etc depending on the state of the record) if the record is versioned.
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @return $this
     */
    protected function buildDefaultActions($gridField, $record)
    {
        GridFieldExtensions::include_requirements();

        $linkCallback = function ($action = null, $hash = null) use ($gridField, $record) {
            $link = Controller::join_links($gridField->Link('item'), $record->ID, $action);
            // @TODO hack workaround: && false here because some JS in the CMS is rewriting
            // a link with a hash in it to the page we're on _now_ #anchor, as opposed to
            // e.g link/set/here#anchor
            return $hash && false ? "$link#$hash" : $link;
        };

        $this->addRootTabActions($record, $linkCallback);
        $this->addVersionedActions($record, $linkCallback);

        return $this;
    }

    /**
     * Add each of the "Root" tabs to the actions for this component
     *
     * We expect that a tabbed list of fields will always have a singular root.
     *
     * @param DataObject $record
     * @param callable $linkCallback
     */
    protected function addRootTabActions(DataObject $record, callable $linkCallback)
    {
        $tabSet = $record->getCMSFields()->first();
        if (!($tabSet instanceof TabSet)) {
            return;
        }

        $first = true;
        foreach ($tabSet->Tabs() as $tab) {
            // Skip the first tab if we've opted to
            if ($first && !$this->getShowFirstTab()) {
                $first = false;
                continue;
            }

            /** @var Tab $tab */
            $tabID = ($first) ? null : $tab->ID();
            $this->addActionToGroup([
                'Title' => $tab->Title(),
                'Link' => $linkCallback('edit', $tabID),
                'Type' => 'link'
            ], 'rootlinks');
            $first = false;
        }
    }

    /**
     * If the object is versioned (has the {@link Versioned} extension applied) then add
     * actions to publish/unpublish etc
     *
     * @param DataObject $record
     * @param callable $linkCallback
     */
    protected function addVersionedActions(DataObject $record, $linkCallback)
    {
        if (!$record->hasExtension(Versioned::class)) {
            return;
        }

        if (!$record->latestPublished()) {
            $this->addActionToGroup([
                'Title' => _t(__CLASS__ . '.Publish', 'Publish'),
                'Link' => $linkCallback('publish'),
                'Type' => 'versioning'
            ], 'versioned');
        }

        if ($record->isPublished()) {
            $this->addActionToGroup([
                'Title' => _t(__CLASS__ . '.Unpublish', 'Unpublish'),
                'Link' => $linkCallback('unpublish'),
                'Type' => 'versioning'
            ], 'versioned');
        }

        $this->addActionToGroup([
            'Title' => _t(__CLASS__ . '.Delete', 'Delete'),
            'Link' => $linkCallback('archive'),
            'Type' => 'versioning'
        ], 'versioned');
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        $this->buildDefaultActions($gridField, $record);

        $templateData = ArrayData::create([
            'Actions' => Convert::raw2json(array_values($this->getActions())),
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
            'item/$ID//publish' => 'handleRecordAction',
            'item/$ID//unpublish' => 'handleRecordAction',
            'item/$ID//archive' => 'handleRecordAction',
            'item/$ID' => 'handleRecordLink',
        ];
    }

    /**
     * Pass through to GridFieldDetailForm::handleItem
     *
     * @param GridField $gridField
     * @param HTTPRequest $request
     */
    public function handleRecordLink($gridField, $request)
    {
        $detailForm = Injector::inst()->create(GridFieldDetailForm::class);
        return $detailForm->handleItem($gridField, $request);
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
        $this->showFirstTab = (bool) $showFirstTab;
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

    public function addActionToGroup(array $action, $groupName)
    {
        if (empty($this->actions[$groupName])) {
            $this->actions[$groupName] = [];
        }

        $this->actions[$groupName][] = $action;

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
