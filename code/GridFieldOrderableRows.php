<?php
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
	GridField_URLHandler {

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
	 * Extra sort fields to apply before the sort field.
	 *
	 * @see setExtraSortFields()
	 * @var string|array
	 */
	protected $extraSortFields = null;

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
	public function __construct($sortField = 'Sort') {
		parent::__construct();
		$this->sortField = $sortField;
	}

	/**
	 * @return string
	 */
	public function getSortField() {
		return $this->sortField;
	}

	/**
	 * Sets the field used to specify the sort.
	 *
	 * @param string $sortField
	 * @return GridFieldOrderableRows $this
	 */
	public function setSortField($field) {
		$this->sortField = $field;
		return $this;
	}

	/**
	 * @return string|array
	 */
	public function getExtraSortFields() {
		return $this->extraSortFields;
	}

	/**
	 * Sets extra sort fields to apply before the sort field.
	 *
	 * @param string|array $fields
	 * @return GridFieldOrderableRows $this
	 */
	public function setExtraSortFields($fields) {
		$this->extraSortFields = $fields;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getReorderColumnNumber() {
		return $this->reorderColumnNumber;
	}

	/**
	 * Sets the number of the column containing the reorder handles.
	 *
	 * @param int $colno
	 * @return GridFieldOrderableRows $this
	 */
	public function setReorderColumnNumber($colno) {
		$this->reorderColumnNumber = $colno;
		return $this;
	}

	/**
	 * Gets the table which contains the sort field.
	 *
	 * @param DataList $list
	 * @return string
	 */
	public function getSortTable(DataList $list) {
		$field = $this->getSortField();

		if($list instanceof ManyManyList) {
			$extra = $list->getExtraFields();
			$table = $list->getJoinTable();

			if($extra && array_key_exists($field, $extra)) {
				return $table;
			}
		}

		$classes = ClassInfo::dataClassesFor($list->dataClass());

		foreach($classes as $class) {
			if(singleton($class)->hasOwnTableDatabaseField($field)) {
				return $class;
			}
		}

		throw new Exception("Couldn't find the sort field '$field'");
	}

	public function getURLHandlers($grid) {
		return array(
			'POST reorder'    => 'handleReorder',
			'POST movetopage' => 'handleMoveToPage'
		);
	}

	/**
	 * @param GridField $field
	 */
	public function getHTMLFragments($field) {
		GridFieldExtensions::include_requirements();

		$field->addExtraClass('ss-gridfield-orderable');
		$field->setAttribute('data-url-reorder', $field->Link('reorder'));
		$field->setAttribute('data-url-movetopage', $field->Link('movetopage'));
	}

	public function augmentColumns($grid, &$cols) {
		if(!in_array('Reorder', $cols) && $grid->getState()->GridFieldOrderableRows->enabled) {
			array_splice($cols, $this->reorderColumnNumber, 0, 'Reorder');
		}
	}

	public function getColumnsHandled($grid) {
		return array('Reorder');
	}

	public function getColumnContent($grid, $record, $col) {
		return ViewableData::create()->renderWith('GridFieldOrderableRowsDragHandle');
	}

	public function getColumnAttributes($grid, $record, $col) {
		return array('class' => 'col-reorder');
	}

	public function getColumnMetadata($grid, $col) {
		return array('title' => '');
	}

	public function getManipulatedData(GridField $grid, SS_List $list) {
		$state = $grid->getState();
		$sorted = (bool) ((string) $state->GridFieldSortableHeader->SortColumn);

		// If the data has not been sorted by the user, then sort it by the
		// sort column, otherwise disable reordering.
		$state->GridFieldOrderableRows->enabled = !$sorted;

		if(!$sorted) {
			$sortterm = '';
			if ($this->extraSortFields) {
				if (is_array($this->extraSortFields)) {
					foreach($this->extraSortFields as $col => $dir) {
						$sortterm .= "$col $dir, ";
					}
				} else {
					$sortterm = $this->extraSortFields.', ';
				}
			}
			$sortterm .= '"'.$this->getSortTable($list).'"."'.$this->getSortField().'"';
			return $list->sort($sortterm);
		} else {
			return $list;
		}
	}

	/**
	 * Handles requests to reorder a set of IDs in a specific order.
	 *
	 * @param GridField $grid
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function handleReorder($grid, $request) {
		$list = $grid->getList();
		$modelClass = $grid->getModelClass();
		if ($list instanceof ManyManyList && !singleton($modelClass)->canView()) {
			$this->httpError(403);
		} else if(!($list instanceof ManyManyList) && !singleton($modelClass)->canEdit()) {
			$this->httpError(403);
		}

		$ids   = $request->postVar('order');
		$field = $this->getSortField();

		if(!is_array($ids)) {
			$this->httpError(400);
		}

		$sortterm = '';
		if ($this->extraSortFields) {
			if (is_array($this->extraSortFields)) {
				foreach($this->extraSortFields as $col => $dir) {
					$sortterm .= "$col $dir, ";
				}
			} else {
				$sortterm = $this->extraSortFields.', ';
			}
		}
		$sortterm .= '"'.$this->getSortTable($list).'"."'.$field.'"';
		$items = $list->filter('ID', $ids)->sort($sortterm);

		// Ensure that each provided ID corresponded to an actual object.
		if(count($items) != count($ids)) {
			$this->httpError(404);
		}

		// Save any un-comitted changes to the gridfield
		if(($form = $grid->getForm()) && ($record = $form->getRecord()) ) {
			$form->loadDataFrom($request->requestVars(), true);
			$grid->saveInto($record);
		}

		// Populate each object we are sorting with a sort value.
		$this->populateSortValues($items);

		// Generate the current sort values.
		if ($items instanceof ManyManyList)
		{
			$current = array();
			foreach ($items->toArray() as $record)
			{
				// NOTE: _SortColumn0 is the first ->sort() field
				//		 used by SS when functions are detected in a SELECT
				//	     or CASE WHEN.
				if (isset($record->_SortColumn0)) {
					$current[$record->ID] = $record->_SortColumn0;
				} else {
					$current[$record->ID] = $record->$field;
				}
			}
		}
		else
		{
			$current = $items->map('ID', $field)->toArray();
		}

		// Perform the actual re-ordering.
		$this->reorderItems($list, $current, $ids);

		return $grid->FieldHolder();
	}

	/**
	 * Handles requests to move an item to the previous or next page.
	 */
	public function handleMoveToPage(GridField $grid, $request) {
		if(!$paginator = $grid->getConfig()->getComponentByType('GridFieldPaginator')) {
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

		if(!isset($values[$id])) {
			$this->httpError(400, 'Invalid item ID');
		}

		$this->populateSortValues($list);

		$page = ((int) $grid->getState()->GridFieldPaginator->currentPage) ?: 1;
		$per  = $paginator->getItemsPerPage();

		if($to == 'prev') {
			$swap = $list->limit(1, ($page - 1) * $per - 1)->first();
			$values[$swap->ID] = $swap->$field;

			$order[] = $id;
			$order[] = $swap->ID;

			foreach($existing as $_id => $sort) {
				if($id != $_id) $order[] = $_id;
			}
		} elseif($to == 'next') {
			$swap = $list->limit(1, $page * $per)->first();
			$values[$swap->ID] = $swap->$field;

			foreach($existing as $_id => $sort) {
				if($id != $_id) $order[] = $_id;
			}

			$order[] = $swap->ID;
			$order[] = $id;
		} else {
			$this->httpError(400, 'Invalid page target');
		}

		$this->reorderItems($list, $values, $order);

		return $grid->FieldHolder();
	}

	protected function reorderItems($list, array $values, array $order) {
		// Get a list of sort values that can be used.
		$pool = array_values($values);
		sort($pool);

		// Loop through each item, and update the sort values which do not
		// match to order the objects.
		foreach(array_values($order) as $pos => $id) {
			if($values[$id] != $pool[$pos]) {
				DB::query(sprintf(
					'UPDATE "%s" SET "%s" = %d WHERE %s',
					$this->getSortTable($list),
					$this->getSortField(),
					$pool[$pos],
					$this->getSortTableClauseForIds($list, $id)
				));
			}
		}

		$this->extend('onAfterReorderItems', $list);
	}

	protected function populateSortValues(DataList $list) {
		$list   = clone $list;
		$field  = $this->getSortField();
		$table  = $this->getSortTable($list);
		$clause = sprintf('"%s"."%s" = 0', $table, $this->getSortField());

		foreach($list->where($clause)->column('ID') as $id) {
			$max = DB::query(sprintf('SELECT MAX("%s") + 1 FROM "%s"', $field, $table));
			$max = $max->value();

			DB::query(sprintf(
				'UPDATE "%s" SET "%s" = %d WHERE %s',
				$table,
				$field,
				$max,
				$this->getSortTableClauseForIds($list, $id)
			));
		}
	}

	protected function getSortTableClauseForIds(DataList $list, $ids) {
		if(is_array($ids)) {
			$value = 'IN (' . implode(', ', array_map('intval', $ids)) . ')';
		} else {
			$value = '= ' . (int) $ids;
		}

		if($list instanceof ManyManyList) {
			$extra = $list->getExtraFields();
			$key   = $list->getLocalKey();
			$foreignKey = $list->getForeignKey();
			$foreignID  = (int) $list->getForeignID();

			if($extra && array_key_exists($this->getSortField(), $extra)) {
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

}
