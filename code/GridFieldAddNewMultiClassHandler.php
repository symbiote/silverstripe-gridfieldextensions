<?php
/**
 * A custom grid field request handler that allows interacting with form fields when adding records.
 */
class GridFieldAddNewMultiClassHandler extends GridFieldDetailForm_ItemRequest {

	public function Link($action = null) {
		if($this->record->ID) {
			return parent::Link($action);
		} else {
			return Controller::join_links(
				$this->gridField->Link(), 'add-multi-class', get_class($this->record)
			);
		}
	}

}
