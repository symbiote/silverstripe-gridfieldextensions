<?php

class GridFieldEditableAddNewButton extends GridFieldAddNewButton implements GridField_URLHandler {
	protected $extraClasses = array();

	/**
	 * Add a CSS-class to the form-container. If needed, multiple classes can
	 * be added by delimiting a string with spaces.
	 *
	 * @param string $class A string containing a classname or several class
	 *                names delimited by a single space.
	 * @return GridFieldEditableAddNewButton $this
	 */
	public function addExtraClass($class) {
		$classes = explode(' ', $class);

		foreach ($classes as $class) {
			$value = trim($class);

			$this->extraClasses[] = $value;
		}

		return $this;
	}

	/**
	 * Compiles all CSS-classes.
	 *
	 * @return string
	 */
	public function extraClass() {
		return implode(array_unique($this->extraClasses), ' ');
	}

	/**
	 * Remove a CSS-class from the form-container. Multiple class names can
	 * be passed through as a space delimited string
	 *
	 * @param string $class
	 * @return GridFieldEditableAddNewButton $this
	 */
	public function removeExtraClass($class) {
		$classes = explode(' ', $class);
		$this->extraClasses = array_diff($this->extraClasses, $classes);
		return $this;
	}

	public function getURLHandlers($gridField) {
		return array(
			'item/new' => 'addNew',
		);
	}

	public function addNew(GridField $g, SS_HTTPRequest $r) {
		$count = $g->getList()->count();
		$record = Object::create($g->getModelClass());
		$g->getList()->add($record);
		if (!$record->canView()) {
			return;
		}
		$rowContent = '';
		foreach ($g->getColumns() as $column) {
			$colContent = $g->getColumnContent($record, $column);
			// A return value of null means this columns should be skipped altogether.
			if ($colContent === null) {
				continue;
			}
			$colAttributes = $g->getColumnAttributes($record, $column);
			$rowContent .= FormField::create_tag('td', $colAttributes, $colContent);
		}
		$classes = array('ss-gridfield-item', 'last');

		if ($count == 0) {
			$classes[] = 'first';
		}
		$classes[] = ($count % 2) ? 'even' : 'odd';
		$row = FormField::create_tag(
			'tr',
			array(
				"class" => implode(' ', $classes),
				'data-id' => $record->ID,
				'data-class' => $record->ClassName,
			),
			$rowContent
		);
		return $row;
	}

	public function getHTMLFragments($gridField) {
		Requirements::javascript('gridfieldextensions/javascript/GridFieldExtensions.js');
		$return = parent::getHTMLFragments($gridField);
		$return[$this->targetFragment] = FormField::create_tag(
			'span',
			array('class' => $this->addExtraClass('ss-gridfield-editable-add-new-button')->extraClass()),
			$return[$this->targetFragment]
		);
		return $return;
	}
}