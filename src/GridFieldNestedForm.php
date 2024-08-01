<?php

namespace Symbiote\GridFieldExtensions;

use Exception;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_DataManipulator;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_SaveHandler;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridFieldStateAware;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\Filterable;
use SilverStripe\ORM\Hierarchy\Hierarchy;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ViewableData;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Gridfield component for nesting GridFields
 */
class GridFieldNestedForm extends AbstractGridFieldComponent implements
    GridField_URLHandler,
    GridField_ColumnProvider,
    GridField_SaveHandler,
    GridField_HTMLProvider,
    GridField_DataManipulator
{
    use Configurable, GridFieldStateAware;

    /**
     * The key used in the post data to identify nested form data
     */
    const POST_KEY = 'GridFieldNestedForm';

    private static $allowed_actions = [
        'handleNestedItem'
    ];

    /**
     * The default max nesting level. Nesting further than this will throw an exception.
     */
    private static int $default_max_nesting_level = 10;

    private string $name;

    private bool $expandNested = false;

    private bool $forceCloseNested = false;

    private GridField $gridField;

    private string $relationName = 'Children';

    private bool $inlineEditable = false;

    /**
     * @var callable|string
     */
    private $canExpandCallback = null;

    private int $maxNestingLevel = 0;

    public function __construct($name = 'NestedForm')
    {
        $this->name = $name;
    }

    /**
     * Get the grid field that this component is attached to
     */
    public function getGridField(): GridField
    {
        return $this->gridField;
    }

    /**
     * Get the relation name to use for the nested grid fields
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }

    /**
     * Set the relation name to use for the nested grid fields
     */
    public function setRelationName(string $relationName): static
    {
        $this->relationName = $relationName;
        return $this;
    }

    /**
     * Get whether the nested grid fields should be inline editable
     */
    public function getInlineEditable(): bool
    {
        return $this->inlineEditable;
    }

    /**
     * Set whether the nested grid fields should be inline editable
     */
    public function setInlineEditable(bool $editable): static
    {
        $this->inlineEditable = $editable;
        return $this;
    }

    /**
     * Set whether the nested grid fields should be expanded by default
     */
    public function setExpandNested(bool $expandNested): static
    {
        $this->expandNested = $expandNested;
        return $this;
    }

    /**
     * Set whether the nested grid fields should be forced closed on load
     */
    public function setForceClosedNested(bool $forceClosed): static
    {
        $this->forceCloseNested = $forceClosed;
        return $this;
    }

    /**
     * Set a callback function to check which items in this grid that should show the expand link
     * for nested gridfields. The callback should return a boolean value.
     * You can either pass a callable or a method name as a string.
     */
    public function setCanExpandCallback(callable|string $callback): static
    {
        $this->canExpandCallback = $callback;
        return $this;
    }

    /**
     * Set the maximum nesting level allowed for nested grid fields
     */
    public function setMaxNestingLevel(int $level): static
    {
        $this->maxNestingLevel = $level;
        return $this;
    }

    /**
     * Get the max nesting level allowed for this grid field.
     */
    public function getMaxNestingLevel(): int
    {
        return $this->maxNestingLevel ?: static::config()->get('default_max_nesting_level');
    }

    /**
     * Check if we are currently at the max nesting level allowed.
     */
    protected function atMaxNestingLevel(GridField $gridField): bool
    {
        $level = 0;
        $controller = $gridField->getForm()->getController();
        $maxLevel = $this->getMaxNestingLevel();
        while ($level < $maxLevel && $controller && $controller instanceof GridFieldNestedFormItemRequest) {
            $controller = $controller->getController();
            $level++;
        }
        return $level >= $maxLevel;
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        return ['title' => ''];
    }

    public function getColumnsHandled($gridField)
    {
        return ['ToggleNested'];
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'col-listChildrenLink grid-field__col-compact'];
    }

    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('ToggleNested', $columns)) {
            array_splice($columns, 0, 0, 'ToggleNested');
        }
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        if ($gridField->getConfig()->getComponentsByType(GridFieldNestedForm::class)->count() > 1) {
            throw new Exception('Only one GridFieldNestedForm component allowed per GridField');
        }
        if ($this->atMaxNestingLevel($gridField)) {
            return '';
        }
        $gridField->addExtraClass('has-nested');
        if ($record->ID && $record->exists()) {
            $this->gridField = $gridField;
            $relationName = $this->getRelationName();
            if (!$record->hasMethod($relationName)) {
                throw new Exception('Invalid relation name');
            }
            if ($this->canExpandCallback) {
                if (is_callable($this->canExpandCallback)
                    && !call_user_func($this->canExpandCallback, $record)
                ) {
                    return '';
                } elseif (is_string($this->canExpandCallback)
                    && $record->hasMethod($this->canExpandCallback)
                    && !$record->{$this->canExpandCallback}($record)
                ) {
                    return '';
                }
            }
            $toggle = 'closed';
            $className = str_replace('\\', '-', get_class($record));
            $state = $gridField->State->GridFieldNestedForm;
            $stateRelation = $className.'-'.$record->ID.'-'.$this->relationName;
            $openState = $state && (int)$state->getData($stateRelation) === 1;
            $forceExpand = $this->expandNested && $record->$relationName()->count() > 0;
            if (!$this->forceCloseNested
                && ($forceExpand || $openState)
            ) {
                $toggle = 'open';
            }

            GridFieldExtensions::include_requirements();

            return ViewableData::create()->customise([
                'Toggle' => $toggle,
                'Link' => $this->Link($record->ID),
                'ToggleLink' => $this->ToggleLink($record->ID),
                'PjaxFragment' => $stateRelation,
                'NestedField' => ($toggle == 'open') ? $this->handleNestedItem($gridField, null, $record): ' '
            ])->renderWith('Symbiote\GridFieldExtensions\GridFieldNestedForm');
        }
    }

    public function getURLHandlers($gridField)
    {
        return [
            'nested/$RecordID/$NestedAction' => 'handleNestedItem',
            'toggle/$RecordID' => 'toggleNestedItem',
            'POST movetoparent' => 'handleMoveToParent'
        ];
    }

    public function getHTMLFragments($gridField)
    {
        /*
         * If we have a DataObject with the hierarchy extension, we want to allow moving items to a new parent.
         * This is enabled by setting the data-url-movetoparent attribute on the grid field, so that the client
         * javascript can handle the move.
         * Implemented in getHTMLFragments since this attribute needs to be added before any rendering happens.
         */
        if (is_a($gridField->getModelClass(), DataObject::class, true)
            && DataObject::has_extension($gridField->getModelClass(), Hierarchy::class)
        ) {
            $gridField->setAttribute('data-url-movetoparent', $gridField->Link('movetoparent'));
        }
        return [];
    }

    /**
     * Handle moving a record to a new parent
     */
    public function handleMoveToParent(GridField $gridField, $request): string
    {
        $move = $request->postVar('move');
        /** @var DataList */
        $list = $gridField->getList();
        $id = isset($move['id']) ? (int) $move['id'] : null;
        if (!$id) {
            throw new HTTPResponse_Exception('Missing ID', 404);
        }
        $to = isset($move['parent']) ? (int)$move['parent'] : null;
        // should be possible either on parent or child grid field, or nested grid field from parent
        $parent = $to ? $list->byID($to) : null;
        if (!$parent
            && $to
            && $gridField->getForm()->getController() instanceof GridFieldNestedFormItemRequest
            && $gridField->getForm()->getController()->getRecord()->ID == $to
        ) {
            $parent = $gridField->getForm()->getController()->getRecord();
        }
        $child = $list->byID($id);
        // we need either a parent or a child, or a move to top level at this stage
        if (!($parent || $child || $to === 0)) {
            throw new HTTPResponse_Exception('Invalid request', 400);
        }
        // parent or child might be from another grid field, so we need to search via DataList in some cases
        if (!$parent && $to) {
            $parent = DataList::create($gridField->getModelClass())->byID($to);
        }
        if (!$child) {
            $child = DataList::create($gridField->getModelClass())->byID($id);
        }
        if ($child) {
            if (!$child->canEdit()) {
                throw new HTTPResponse_Exception('Not allowed', 403);
            }
            if ($child->hasExtension(Hierarchy::class)) {
                $child->ParentID = $parent ? $parent->ID : 0;
            }
            // validate that the record is still valid
            $validationResult = $child->validate();
            if ($validationResult->isValid()) {
                if ($child->hasExtension(Versioned::class)) {
                    $child->writeToStage(Versioned::DRAFT);
                } else {
                    $child->write();
                }

                // reorder items at the same time, if applicable
                /** @var GridFieldOrderableRows */
                $orderableRows = $gridField->getConfig()->getComponentByType(GridFieldOrderableRows::class);
                if ($orderableRows) {
                    $orderableRows->setImmediateUpdate(true);
                    try {
                        $orderableRows->handleReorder($gridField, $request);
                    } catch (Exception $e) {
                    }
                }
            } else {
                $messages = $validationResult->getMessages();
                $message = array_pop($messages);
                throw new HTTPResponse_Exception($message['message'], 400);
            }
        }
        return $gridField->FieldHolder();
    }

    /**
     * Handle the request to show a nested item
     */
    public function handleNestedItem(
        GridField $gridField,
        HTTPRequest|null $request = null,
        ViewableData|null $record = null
    ): HTTPResponse|RequestHandler|Form {
        if ($this->atMaxNestingLevel($gridField)) {
            throw new Exception('Max nesting level reached');
        }
        $list = $gridField->getList();
        if (!$record && $request && $list instanceof Filterable) {
            $recordID = $request->param('RecordID');
            $record = $list->byID($recordID);
        }
        if (!$record) {
            return '';
        }
        $relationName = $this->getRelationName();
        if (!$record->hasMethod($relationName)) {
            throw new Exception('Invalid relation name');
        }
        $manager = $this->getStateManager();
        $stateRequest = $request ?: $gridField->getForm()->getRequestHandler()->getRequest();
        if ($gridStateStr = $manager->getStateFromRequest($gridField, $stateRequest)) {
            $gridField->getState(false)->setValue($gridStateStr);
        }
        $this->gridField = $gridField;
        $itemRequest = GridFieldNestedFormItemRequest::create(
            $gridField,
            $this,
            $record,
            $gridField->getForm()->getController(),
            $this->name
        );
        if ($request) {
            $pjaxFragment = $request->getHeader('X-Pjax');
            $targetPjaxFragment = str_replace('\\', '-', get_class($record)).'-'.$record->ID.'-'.$this->relationName;
            if ($pjaxFragment == $targetPjaxFragment) {
                $pjaxReturn = [$pjaxFragment => $itemRequest->ItemEditForm()->Fields()->first()->forTemplate()];
                $response = new HTTPResponse(json_encode($pjaxReturn));
                $response->addHeader('Content-Type', 'text/json');
                return $response;
            } else {
                return $itemRequest->ItemEditForm();
            }
        } else {
            return $itemRequest->ItemEditForm()->Fields()->first();
        }
    }

    /**
     * Handle the request to toggle a nested item in the gridfield state
     */
    public function toggleNestedItem(
        GridField $gridField,
        HTTPRequest|null $request = null,
        ViewableData|null $record = null
    ) {
        $list = $gridField->getList();
        if (!$record && $request && $list instanceof Filterable) {
            $recordID = $request->param('RecordID');
            $record = $list->byID($recordID);
        }
        $manager = $this->getStateManager();
        if ($gridStateStr = $manager->getStateFromRequest($gridField, $request)) {
            $gridField->getState(false)->setValue($gridStateStr);
        }
        $className = str_replace('\\', '-', get_class($record));
        $state = $gridField->getState()->GridFieldNestedForm;
        $stateRelation = $className.'-'.$record->ID.'-'.$this->getRelationName();
        $state->$stateRelation = (int)$request->getVar('toggle');
    }

    /**
     * Get the link for the nested grid field
     */
    public function Link($action = null): string
    {
        $link = Director::absoluteURL(Controller::join_links($this->gridField->Link('nested'), $action));
        $manager = $this->getStateManager();
        return $manager->addStateToURL($this->gridField, $link);
    }

    /**
     * Get the link for the toggle action
     */
    public function ToggleLink($action = null): string
    {
        $link = Director::absoluteURL(Controller::join_links($this->gridField->Link('toggle'), $action, '?toggle='));
        $manager = $this->getStateManager();
        return $manager->addStateToURL($this->gridField, $link);
    }

    public function handleSave(GridField $gridField, DataObjectInterface $record)
    {
        $postKey = GridFieldNestedForm::POST_KEY;
        $value = $gridField->Value();
        if (isset($value['GridState']) && $value['GridState']) {
            // set grid state from value, to store open/closed toggle state for nested forms
            $gridField->getState(false)->setValue($value['GridState']);
        }
        $manager = $this->getStateManager();
        $request = $gridField->getForm()->getRequestHandler()->getRequest();
        if ($gridStateStr = $manager->getStateFromRequest($gridField, $request)) {
            $gridField->getState(false)->setValue($gridStateStr);
        }
        foreach ($request->postVars() as $key => $val) {
            $list = $gridField->getList();
            if ($list instanceof Filterable
                && preg_match("/{$gridField->getName()}-{$postKey}-(\d+)/", $key, $matches)
            ) {
                $recordID = $matches[1];
                $nestedData = $val;
                $record = $list->byID($recordID);
                if ($record) {
                    /** @var GridField */
                    $nestedGridField = $this->handleNestedItem($gridField, null, $record);
                    $nestedGridField->setValue($nestedData);
                    $nestedGridField->saveInto($record);
                }
            }
        }
    }

    public function getManipulatedData(GridField $gridField, SS_List $dataList)
    {
        if ($this->relationName == 'Children'
            && is_a($gridField->getModelClass(), DataObject::class, true)
            && DataObject::has_extension($gridField->getModelClass(), Hierarchy::class)
            && $gridField->getForm()->getController() instanceof ModelAdmin
            && $dataList instanceof Filterable
        ) {
            $dataList = $dataList->filter('ParentID', 0);
        }
        return $dataList;
    }
}
