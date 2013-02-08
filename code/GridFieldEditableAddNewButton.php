<?php

class GridFieldEditableAddNewButton extends GridFieldAddNewButton implements GridField_URLHandler {
	public function getURLHandlers($gridField) {
		return array(
			'item/new' => 'addNew',
		);
	}

	public function addNew(GridField $g, SS_HTTPRequest $r) {
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
		$count = $g->getList()->count();
		if ($count == 0) {
			$classes[] = 'first';
		}
		$classes[] = (++$count % 2) ? 'even' : 'odd';
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
			array('class' => 'ss-gridfield-editable-add-new-button'),
			$return[$this->targetFragment]
		);
		return $return;
	}
}