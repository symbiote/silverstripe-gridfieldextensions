<?php

/**
 * Duplicate the selected row
 *
 * @author Gabriele Brosulo <gabriele@brosulo.net>
 * @creation-date 21-apr-2017
 */
class GridFieldDuplicateRow implements GridField_ColumnProvider, GridField_ActionProvider {
    /* ******************************
     * ** GridField_ColumnProvider **
     * ******************************/

    public function augmentColumns($gridField, &$columns) {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName) {
        return array('class' => 'col-buttons');
    }

    public function getColumnMetadata($gridField, $columnName) {
        if ($columnName == 'Actions') {
            return array('title' => '');
        }
    }

    public function getColumnsHandled($gridField) {
        return array('Actions');
    }

    public function getColumnContent($gridField, $record, $columnName) {
        if (!$record->canEdit())
            return;

        $field = GridField_FormAction::create(
                        $gridField, 'DuplicateRow' . $record->ID, 'Clone', "duplicaterow", array('RecordID' => $record->ID)
        );

        return $field->Field();
    }

    /* *****************************
     * **GridField_ActionProvider **
     * ******************************/

    public function getActions($gridField) {
        return array('duplicaterow');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {

        if ($actionName == 'duplicaterow') {

            $model = $gridField->getModelClass();

            $obj = DataObject::get_by_id($model, $arguments['RecordID']);
            $obj->duplicate();

            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                    200, 'Duplicate Record Done.'
            );
        }
    }

}
