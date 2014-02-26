<?php
/**
 * Builds on the {@link GridFieldEditableColumns} component to allow creating new records.
 */
class GridFieldAddNewInlineButton implements GridField_HTMLProvider, GridField_SaveHandler {

	private $fragment;

	private $title;

	/**
	 * @param string $fragment the fragment to render the button in
	 */
	public function __construct($fragment = 'buttons-before-left') {
		$this->setFragment($fragment);
		$this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));
	}

	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldAddNewInlineButton $this
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * Gets the button title text.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the button title text.
	 *
	 * @param string $title
	 * @return GridFieldAddNewInlineButton $this
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	public function getHTMLFragments($grid) {
		if($grid->getList() && !singleton($grid->getModelClass())->canCreate()) {
			return array();
		}

		$fragment = $this->getFragment();

		if(!$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns')) {
			throw new Exception('Inline adding requires the editable columns component');
		}

		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js');
		GridFieldExtensions::include_requirements();

		$data = new ArrayData(array(
			'Title'  => $this->getTitle(),
		));

		return array(
			$fragment => $data->renderWith(__CLASS__),
			'after'   => $this->getRowTemplate($grid, $editable)
		);
	}

	private function getRowTemplate(GridField $grid, GridFieldEditableColumns $editable) {
		$columns = new ArrayList();
		$handled = array_keys($editable->getDisplayFields($grid));

		if($grid->getList()) {
			$record = Object::create($grid->getModelClass());
		} else {
			$record = null;
		}

		$fields = $editable->getFields($grid, $record);

		foreach($grid->getColumns() as $column) {
			if(in_array($column, $handled)) {
				$field = $fields->dataFieldByName($column);
				$field->setName(sprintf(
					'%s[%s][{%%=o.num%%}][%s]', $grid->getName(), __CLASS__, $field->getName()
				));

				$content = $field->Field();
			} else {
				$content = null;
			}

			$attrs = '';

			foreach($grid->getColumnAttributes($record, $column) as $attr => $val) {
				$attrs .= sprintf(' %s="%s"', $attr, Convert::raw2att($val));
			}

			$columns->push(new ArrayData(array(
				'Content'    => $content,
				'Attributes' => $attrs,
				'IsActions'  => $column == 'Actions'
			)));
		}

		return $columns->renderWith('GridFieldAddNewInlineRow');
	}

	public function handleSave(GridField $grid, DataObjectInterface $record) {
		$list  = $grid->getList();
		$value = $grid->Value();

		if(!isset($value[__CLASS__]) || !is_array($value[__CLASS__])) {
			return;
		}

		$class    = $grid->getModelClass();
		$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns');
		$form     = $editable->getForm($grid, $record);

		if(!singleton($class)->canCreate()) {
			return;
		}

		foreach($value[__CLASS__] as $fields) {
			$item  = $class::create();
			$extra = array();

			$form->loadDataFrom($fields, Form::MERGE_CLEAR_MISSING);
			$form->saveInto($item);

			if($list instanceof ManyManyList) {
				$extra = array_intersect_key($form->getData(), (array) $list->getExtraFields());
			}

			$item->write();
			$list->add($item, $extra);
		}
	}

}
