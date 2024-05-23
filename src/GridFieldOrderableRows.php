<?php

namespace Symbiote\GridFieldExtensions;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_DataManipulator;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_SaveHandler;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\ORM\ManyManyThroughQueryManipulator;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ViewableData;

/**
 * Allows grid field rows to be re-ordered via drag and drop. Both normal data
 * lists and many many lists can be ordered.
 *
 * If the grid field has not been sorted, this component will sort the data by
 * the sort field.
 */
class GridFieldOrderableRows extends RequestHandler implements
    GridField_ColumnProvider,
    GridField_DataManipulator,
    GridField_HTMLProvider,
    GridField_URLHandler,
    GridField_SaveHandler
{

    /**
     * @see $immediateUpdate
     * @var boolean
     */
    private static $default_immediate_update = true;

    private static $allowed_actions = array(
        'handleReorder',
        'handleMoveToPage'
    );

    /**
     * The database field which specifies the sort, defaults to "Sort".
     *
     * @see setSortField()
     * @var string
     */
    protected $sortField;

    /**
     * If set to true, when an item is re-ordered, it will update on the
     * database and refresh the gridfield. When set to false, it will only
     * update the sort order when the record is saved.
     *
     * @var boolean
     */
    protected $immediateUpdate;

    /**
     * Extra sort fields to apply before the sort field.
     *
     * @see setExtraSortFields()
     * @var string|array
     */
    protected $extraSortFields = null;

    /**
     * If the items in the list are versioned and this is set to true, then
     * we will check to see if the version we're sorting is the latest published
     * version and if so then we will re-publish the item.
     *
     * @var boolean
     */
    protected $republishLiveRecords = false;

    /**
     * The number of the column containing the reorder handles
     *
     * @see setReorderColumnNumber()
     * @var int
     */
    protected $reorderColumnNumber = 0;

    /**
     * @param string $sortField
     */
    public function __construct($sortField = 'Sort')
    {
        parent::__construct();
        $this->sortField = $sortField;
        $this->immediateUpdate = $this->config()->default_immediate_update;
    }

    /**
     * @return string
     */
    public function getSortField()
    {
        return $this->sortField;
    }

    /**
     * Sets the field used to specify the sort.
     *
     * @param string $sortField
     * @return GridFieldOrderableRows $this
     */
    public function setSortField($field)
    {
        $this->sortField = $field;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getImmediateUpdate()
    {
        return $this->immediateUpdate;
    }

    /**
     * @see $immediateUpdate
     * @param boolean $immediateUpdate
     * @return GridFieldOrderableRows $this
     */
    public function setImmediateUpdate($bool)
    {
        $this->immediateUpdate = $bool;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getExtraSortFields()
    {
        return $this->extraSortFields;
    }

    /**
     * @see $republishLiveRecords
     *
     * @return boolean
     */
    public function getRepublishLiveRecords()
    {
        return $this->republishLiveRecords;
    }

    /**
     * @see $republishLiveRecords
     *
     * @param boolean $bool
     * @return GridFieldOrderableRows $this
     */
    public function setRepublishLiveRecords($bool)
    {
        $this->republishLiveRecords = $bool;
        return $this;
    }

    /**
     * Checks to see if the relationship list is for a type of many_many
     *
     * @param SS_List $list
     *
     * @return bool
     */
    protected function isManyMany(SS_List $list)
    {
        return $list instanceof ManyManyList || $list instanceof ManyManyThroughList;
    }

    /**
     * Sets extra sort fields to apply before the sort field.
     *
     * @param string|array $fields
     * @return GridFieldOrderableRows $this
     */
    public function setExtraSortFields($fields)
    {
        $this->extraSortFields = $fields;
        return $this;
    }

    /**
     * @return int
     */
    public function getReorderColumnNumber()
    {
        return $this->reorderColumnNumber;
    }

    /**
     * Sets the number of the column containing the reorder handles.
     *
     * @param int $colno
     * @return GridFieldOrderableRows $this
     */
    public function setReorderColumnNumber($colno)
    {
        $this->reorderColumnNumber = $colno;
        return $this;
    }

    /**
     * Validates sortable list
     *
     * @param SS_List $list
     * @throws Exception
     */
    public function validateSortField(SS_List $list)
    {
        $field = $this->getSortField();

        // Check extra fields on many many relation types
        if ($list instanceof ManyManyList) {
            $extra = $list->getExtraFields();

            if ($extra && array_key_exists($field, $extra ?? [])) {
                return;
            }
        } elseif ($list instanceof ManyManyThroughList) {
            $manipulator = $this->getManyManyInspector($list);
            $fieldTable = DataObject::getSchema()->tableForField($manipulator->getJoinClass(), $field);
            if ($fieldTable) {
                return;
            }
        }

        $classes = ClassInfo::dataClassesFor($list->dataClass());

        foreach ($classes as $class) {
            if (singleton($class)->hasDataBaseField($field)) {
                return;
            }
        }

        throw new Exception("Couldn't find the sort field '" . $field . "'");
    }

    /**
     * Gets the table which contains the sort field.
     *
     * @param DataList $list
     * @return string
     */
    public function getSortTable(SS_List $list)
    {
        $field = $this->getSortField();
        if ($list instanceof ManyManyList) {
            $extra = $list->getExtraFields();
            $table = $list->getJoinTable();
            if ($extra && array_key_exists($field, $extra ?? [])) {
                return $table;
            }
        } elseif ($list instanceof ManyManyThroughList) {
            return $this->getManyManyInspector($list)->getJoinAlias();
        }
        $classes = ClassInfo::dataClassesFor($list->dataClass());
        foreach ($classes as $class) {
            if (singleton($class)->hasDataBaseField($field)) {
                return DataObject::getSchema()->tableName($class);
            }
        }
        throw new Exception("Couldn't find the sort field '$field'");
    }

    public function getURLHandlers($grid)
    {
        return array(
            'POST reorder'    => 'handleReorder',
            'POST movetopage' => 'handleMoveToPage'
        );
    }

    /**
     * @param GridField $field
     */
    public function getHTMLFragments($field)
    {
        GridFieldExtensions::include_requirements();

        $field->addExtraClass('ss-gridfield-orderable');
        $field->setAttribute('data-immediate-update', (string)(int)$this->immediateUpdate);
        $field->setAttribute('data-url-reorder', $field->Link('reorder'));
        $field->setAttribute('data-url-movetopage', $field->Link('movetopage'));
    }

    public function augmentColumns($grid, &$cols)
    {
        if (!in_array('Reorder', $cols ?? []) && $grid->getState()->GridFieldOrderableRows->enabled) {
            array_splice($cols, $this->reorderColumnNumber ?? 0, 0, 'Reorder');
        }
    }

    public function getColumnsHandled($grid)
    {
        return array('Reorder');
    }

    public function getColumnContent($grid, $record, $col)
    {
        // In case you are using GridFieldEditableColumns, this ensures that
        // the correct sort order is saved. If you are not using that component,
        // this will be ignored by other components, but will still work for this.
        $sortFieldName = sprintf(
            '%s[GridFieldEditableColumns][%s][%s]',
            $grid->getName(),
            $record->ID,
            $this->getSortField()
        );

        // Default: Get the sort field directly from the current record
        $currentSortValue = $record->getField($this->getSortField());

        $list = $grid->getList();
        if ($list instanceof ManyManyThroughList) {
            // In a many many through list we should get the current sort order from the relationship
            // if it exists, not directly from the record
            $throughListSorts = $this->getSortValuesFromManyManyThroughList($list, $this->getSortField());

            if (array_key_exists($record->ID, $throughListSorts ?? [])) {
                $currentSortValue = $throughListSorts[$record->ID];
            }
        }

        $sortField = HiddenField::create($sortFieldName, false, $currentSortValue);
        $sortField->addExtraClass('ss-orderable-hidden-sort');
        $sortField->setForm($grid->getForm());

        return ViewableData::create()->customise(array(
            'SortField' => $sortField
        ))->renderWith('Symbiote\\GridFieldExtensions\\GridFieldOrderableRowsDragHandle');
    }

    public function getColumnAttributes($grid, $record, $col)
    {
        return array('class' => 'col-reorder');
    }

    public function getColumnMetadata($grid, $col)
    {
        if ($fieldLabels = singleton($grid->getModelClass())->fieldLabels()) {
            return array('title' => isset($fieldLabels['Reorder']) ? $fieldLabels['Reorder'] : '');
        }

        return array('title' => '');
    }

    public function getManipulatedData(GridField $grid, SS_List $list)
    {
        $state = $grid->getState();
        $sorted = (bool) ((string) $state->GridFieldSortableHeader->SortColumn);

        // If the data has not been sorted by the user, then sort it by the
        // sort column, otherwise disable reordering.
        $state->GridFieldOrderableRows->enabled = !$sorted;

        if (!$sorted) {
            $sortterm = '';
            if ($this->extraSortFields) {
                if (is_array($this->extraSortFields)) {
                    foreach ($this->extraSortFields as $col => $dir) {
                        $sortterm .= "$col $dir, ";
                    }
                } else {
                    $sortterm = $this->extraSortFields.', ';
                }
            }
            if ($list instanceof ArrayList) {
                // Fix bug in 3.1.3+ where ArrayList doesn't account for quotes
                $sortterm .= $this->getSortTable($list).'.'.$this->getSortField();
            } else {
                $sortterm .= '"'.$this->getSortTable($list).'"."'.$this->getSortField().'"';

                if ($list instanceof DataList) {
                    $classname = $list->dataClass();
                    if ($defaultSort = Config::inst()->get($classname, 'default_sort')) {
                        if (is_array($defaultSort)) {
                            $defaultSortArray = [];
                            foreach ($defaultSort as $column => $direction) {
                                $defaultSortArray[] = "\"$column\" $direction";
                            }
                            $defaultSort = implode(', ', $defaultSortArray);
                        }
                        // Append the default sort to the end of the sort string
                        // This may result in redundancy... but it seems to work
                        $sortterm .= ($sortterm ? ', ' : '') . $defaultSort;
                    }
                }
            }

            if ($list instanceof DataList) {
                return $list->orderBy($sortterm);
            } elseif (method_exists($list, 'sort')) {
                return $list->sort($sortterm);
            }
        }

        return $list;
    }

    /**
     * Handles requests to reorder a set of IDs in a specific order.
     *
     * @param GridField $grid
     * @param HTTPRequest $request
     * @return string
     * @throws HTTPResponse_Exception
     */
    public function handleReorder($grid, $request)
    {
        if (!$this->immediateUpdate) {
            $this->httpError(400);
        }
        $list = $grid->getList();
        $modelClass = $grid->getModelClass();
        $isManyMany = $this->isManyMany($list);
        if ($isManyMany && !singleton($modelClass)->canView()) {
            $this->httpError(403);
        } elseif (!$isManyMany && !singleton($modelClass)->canEdit()) {
            $this->httpError(403);
        }

        // Save any un-committed changes to the gridfield
        if (($form = $grid->getForm()) && ($record = $form->getRecord())) {
            $form->loadDataFrom($request->requestVars(), true);
            $grid->saveInto($record);
        }

        // Get records from the `GridFieldEditableColumns` column
        $gridFieldName = $grid->getName();
        if (strpos($gridFieldName ?? '', '.') !== false) {
            $gridFieldName = str_replace('.', '_', $gridFieldName ?? '');
        }

        $data = $request->postVar($gridFieldName);
        $sortedIDs = $this->getSortedIDs($data);
        if (!$this->executeReorder($grid, $sortedIDs)) {
            $this->httpError(400);
        }

        Controller::curr()->getResponse()->addHeader(
            'X-Status',
            rawurlencode(_t(__CLASS__ . '.REORDERED', 'Records reordered.'))
        );
        return $grid->FieldHolder();
    }

    /**
     * Get mapping of sort value to item ID from posted data (gridfield list state), ordered by sort value.
     *
     * @param array $data Raw posted data
     * @return array [sortIndex => recordID]
     */
    protected function getSortedIDs($data)
    {
        if (empty($data['GridFieldEditableColumns'])) {
            return array();
        }

        $sortedIDs = array();
        foreach ($data['GridFieldEditableColumns'] as $id => $recordData) {
            $sortValue = $recordData[$this->sortField];
            $sortedIDs[$sortValue] = $id;
        }
        ksort($sortedIDs);
        return $sortedIDs;
    }

    /**
     * Handles requests to move an item to the previous or next page.
     */
    public function handleMoveToPage(GridField $grid, $request)
    {
        if (!$paginator = $grid->getConfig()->getComponentByType(GridFieldPaginator::class)) {
            $this->httpError(404, 'Paginator component not found');
        }

        $move  = $request->postVar('move');
        $field = $this->getSortField();

        $list  = $grid->getList();
        $manip = $grid->getManipulatedList();

        $existing = $manip->map('ID', $field)->toArray();
        $values   = $existing;
        $order    = array();

        $id = isset($move['id']) ? (int) $move['id'] : null;
        $to = isset($move['page']) ? $move['page'] : null;

        if (!isset($values[$id])) {
            $this->httpError(400, 'Invalid item ID');
        }

        $this->populateSortValues($list);

        $page = ((int) $grid->getState()->GridFieldPaginator->currentPage) ?: 1;
        $per  = $paginator->getItemsPerPage();

        if ($to == 'prev') {
            $swap = $list->limit(1, ($page - 1) * $per - 1)->first();
            $order[$swap->$field] = $id;

            reset($existing);
            $isMovingFirstItemOnPage = (key($existing) == $id);
            $swappedItemNewSort = current($existing);
            $order[$swappedItemNewSort] = $swap->ID;

            // We want the item that's being swapped from the previous page to appear at the start
            // of the current page, so we have to adjust the sort order of all the items between the
            // start of the page and the location of the item we're moving
            if (!$isMovingFirstItemOnPage) {
                foreach ($existing as $_id => $sort) {
                    if ($id == $_id) {
                        break;
                    }
                    $order[$sort + 1] = $_id;
                }
            }
        } elseif ($to == 'next') {
            $swap = $list->limit(1, $page * $per)->first();
            $order[$swap->$field] = $id;

            end($existing);
            $isMovingLastItemOnPage = (key($existing) == $id);
            $swappedItemNewSort = current($existing);
            $order[$swappedItemNewSort] = $swap->ID;

            // We want the item that's being swapped from the next page to appear at the end
            // of the current page, so we have to adjust the sort order of all the items between the
            // end of the page and the location of the item we're moving
            if (!$isMovingLastItemOnPage) {
                foreach (array_reverse($existing, true) as $_id => $sort) {
                    if ($id == $_id) {
                        break;
                    }
                    $order[$sort - 1] = $_id;
                }
            }
        } else {
            $this->httpError(400, 'Invalid page target');
        }

        $this->reorderItems($list, $values, $order);

        return $grid->FieldHolder();
    }

    /**
     * Handle saving when 'immediateUpdate' is disabled, otherwise this isn't
     * necessary for the default sort mode.
     */
    public function handleSave(GridField $grid, DataObjectInterface $record)
    {
        if (!$this->immediateUpdate) {
            $value = $grid->Value();
            $sortedIDs = $this->getSortedIDs($value);
            if ($sortedIDs) {
                $this->executeReorder($grid, $sortedIDs);
            }
        }
    }

    /**
     * @param GridField $grid
     * @param array $sortedIDs List of IDS, where the key is the sort field value to save
     * @return bool
     */
    protected function executeReorder(GridField $grid, $sortedIDs)
    {
        if (!is_array($sortedIDs) || empty($sortedIDs)) {
            return false;
        }
        $sortField = $this->getSortField();

        $sortterm = '';
        if ($this->extraSortFields) {
            if (is_array($this->extraSortFields)) {
                foreach ($this->extraSortFields as $col => $dir) {
                    $sortterm .= "$col $dir, ";
                }
            } else {
                $sortterm = $this->extraSortFields.', ';
            }
        }
        $list = $grid->getList();
        $sortterm .= '"'.$this->getSortTable($list).'"."'.$sortField.'"';
        $items = $list->filter('ID', $sortedIDs)->orderBy($sortterm);

        // Ensure that each provided ID corresponded to an actual object.
        if (count($items ?? []) != count($sortedIDs ?? [])) {
            return false;
        }

        // Populate each object we are sorting with a sort value.
        $this->populateSortValues($items);

        // Generate the current sort values.
        if ($items instanceof ManyManyList) {
            $current = array();
            foreach ($items->toArray() as $record) {
                // NOTE: _SortColumn0 is the first ->sort() field
                //         used by SS when functions are detected in a SELECT
                //         or CASE WHEN.
                if (isset($record->_SortColumn0)) {
                    $current[$record->ID] = $record->_SortColumn0;
                } else {
                    $current[$record->ID] = $record->$sortField;
                }
            }
        } elseif ($items instanceof ManyManyThroughList) {
            $current = $this->getSortValuesFromManyManyThroughList($list, $sortField);
        } else {
            $current = $items->map('ID', $sortField)->toArray();
        }

        // Perform the actual re-ordering.
        $this->reorderItems($list, $current, $sortedIDs);
        return true;
    }

    /**
     * @param SS_List $list
     * @param array $values **UNUSED** [listItemID => currentSortValue];
     * @param array $sortedIDs [newSortValue => listItemID]
     */
    protected function reorderItems($list, array $values, array $sortedIDs)
    {
        $this->extend('onBeforeReorderItems', $list, $values, $sortedIDs);

        // setup
        $sortField = $this->getSortField();
        // The problem is that $sortedIDs is a list of the _related_ item IDs, which causes trouble
        // with ManyManyThrough, where we need the ID of the _join_ item in order to set the value.
        $itemToSortReference = ($list instanceof ManyManyThroughList) ? 'getJoin' : 'Me';
        $currentSortList = $list->map('ID', $itemToSortReference)->toArray();

        // sanity check.
        $this->validateSortField($list);

        // ManyManyList extra fields aren't easily updated via the ORM, and so they need to be updated through an SQL
        // Query
        if ($list instanceof ManyManyList) {
            $sortTable = $this->getSortTable($list);

            // Loop through each item, and update the sort values which do not match to order the objects.
            foreach ($sortedIDs as $newSortValue => $targetRecordID) {
                if ($currentSortList[$targetRecordID]->$sortField != $newSortValue) {
                    DB::query(sprintf(
                        'UPDATE "%s" SET "%s" = %d WHERE %s',
                        $sortTable,
                        $sortField,
                        $newSortValue,
                        $this->getSortTableClauseForIds($list, $targetRecordID)
                    ));
                }
            }
        } else {
            // For versioned objects, modify them with the ORM so that the
            // *_Versions table is updated. This ensures re-ordering works
            // similar to the SiteTree where you change the position, and then
            // you go into the record and publish it.
            foreach ($sortedIDs as $newSortValue => $targetRecordID) {
                // either the list data class (has_many, (belongs_)many_many)
                // or the intermediary join class (many_many through)
                $record = $currentSortList[$targetRecordID];

                if ($record->$sortField != $newSortValue) {
                    $record->$sortField = $newSortValue;

                    // We need to do this before writing otherwith isLiveVersion() will always be false
                    $shouldRepublish = $this->getRepublishLiveRecords() && $record->isLiveVersion();

                    // Write our staged record and publish if required
                    $record->write();
                    if ($shouldRepublish) {
                        $record->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE, true);
                    }
                }
            }
        }

        $this->extend('onAfterReorderItems', $list, $values, $sortedIDs);
    }

    protected function populateSortValues(DataList $list)
    {
        $list   = clone $list;
        $field  = $this->getSortField();
        $table  = $this->getSortTable($list);
        $clause = sprintf('"%s"."%s" = 0', $table, $this->getSortField());
        $now = DBDatetime::now()->Rfc2822();
        $additionalSQL = '';
        $baseTable = DataObject::getSchema()->baseDataTable($list->dataClass());

        $isBaseTable = ($baseTable == $table);
        if (!$list instanceof ManyManyList && $isBaseTable) {
            $additionalSQL = ", \"LastEdited\" = '$now'";
        }

        foreach ($list->where($clause)->column('ID') as $id) {
            $max = DB::query(sprintf('SELECT MAX("%s") + 1 FROM "%s"', $field, $table));
            $max = $max->value();

            DB::query(sprintf(
                'UPDATE "%s" SET "%s" = %d%s WHERE %s',
                $table,
                $field,
                $max,
                $additionalSQL,
                $this->getSortTableClauseForIds($list, $id)
            ));

            if (!$isBaseTable && !$this->isManyMany($list)) {
                DB::query(sprintf(
                    'UPDATE "%s" SET "LastEdited" = \'%s\' WHERE %s',
                    $baseTable,
                    $now,
                    $this->getSortTableClauseForIds($list, $id)
                ));
            }
        }
    }

    /**
     * Forms a WHERE clause for the table the sort column is defined on.
     * e.g. ID = 5
     * e.g. ID IN(5, 8, 10)
     * e.g. SortOrder = 5 AND RelatedThing.ID = 3
     * e.g. SortOrder IN(5, 8, 10) AND RelatedThing.ID = 3
     *
     * @param DataList $list
     * @param int|string|array $ids a single number, or array of numbers
     *
     * @return string
     */
    protected function getSortTableClauseForIds(DataList $list, $ids)
    {
        if (is_array($ids)) {
            $value = 'IN (' . implode(', ', array_map('intval', $ids ?? [])) . ')';
        } else {
            $value = '= ' . (int) $ids;
        }

        if ($this->isManyMany($list)) {
            $introspector = $this->getManyManyInspector($list);
            $extra = $list instanceof ManyManyList ?
                $introspector->getExtraFields() :
                DataObjectSchema::create()->fieldSpecs($introspector->getJoinClass(), DataObjectSchema::DB_ONLY);
            $key   = $introspector->getLocalKey();
            $foreignKey = $this->getManyManyInspectorForeignKey($introspector);
            $foreignID  = (int) $list->getForeignID();

            if ($extra && array_key_exists($this->getSortField(), $extra ?? [])) {
                return sprintf(
                    '"%s" %s AND "%s" = %d',
                    $key,
                    $value,
                    $foreignKey,
                    $foreignID
                );
            }
        }

        return "\"ID\" $value";
    }

    /**
     * A ManyManyList defines functions such as getLocalKey, however on ManyManyThroughList
     * these functions are moved to ManyManyThroughQueryManipulator, but otherwise retain
     * the same signature.
     *
     * @param ManyManyList|ManyManyThroughList $list
     *
     * @return ManyManyList|ManyManyThroughQueryManipulator
     */
    protected function getManyManyInspector($list)
    {
        $inspector = $list;
        if ($list instanceof ManyManyThroughList) {
            foreach ($list->dataQuery()->getDataQueryManipulators() as $manipulator) {
                if ($manipulator instanceof ManyManyThroughQueryManipulator) {
                    $inspector = $manipulator;
                    break;
                }
            }
        }
        return $inspector;
    }


    /**
     * Depending on the list inspector and the list itself (ManyMany vs ManyManyThrough), the method to obtain
     * the foreign key may be different.
     *
     * @param $inspector
     * @return string
     */
    private function getManyManyInspectorForeignKey($inspector)
    {
        if (($inspector instanceof ManyManyThroughQueryManipulator) && (method_exists($inspector, 'getForeignIDKey'))) {
            // This method has been introduced in framework 4.1
            return $inspector->getForeignIDKey();
        }

        return $inspector->getForeignKey();
    }

    /**
     * Used to get sort orders from a many many through list relationship record, rather than the current
     * record itself.
     *
     * @param ManyManyList|ManyManyThroughList $list
     * @return int[] Sort orders for the
     */
    protected function getSortValuesFromManyManyThroughList($list, $sortField)
    {
        $manipulator = $this->getManyManyInspector($list);

        // Find the foreign key name, ID and class to look up
        $joinClass = $manipulator->getJoinClass();
        $fromRelationName = $this->getManyManyInspectorForeignKey($manipulator);
        $toRelationName = $manipulator->getLocalKey();

        // Create a list of the MMTL relations
        $sortlist = DataList::create($joinClass)->filter([
            $toRelationName => $list->column('ID'),
            // first() is safe as there are earlier checks to ensure our list to sort is valid
            $fromRelationName => $list->first()->getJoin()->$fromRelationName,
        ]);

        return $sortlist->map($toRelationName, $sortField)->toArray();
    }
}
