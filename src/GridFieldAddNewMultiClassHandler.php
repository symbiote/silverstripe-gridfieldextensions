<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

/**
 * A custom grid field request handler that allows interacting with form fields when adding records.
 */
class GridFieldAddNewMultiClassHandler extends GridFieldDetailForm_ItemRequest
{

    public function Link($action = null)
    {
        if ($this->record->ID) {
            return parent::Link($action);
        } else {
            return Controller::join_links(
                $this->gridField->Link(),
                'add-multi-class',
                $this->sanitiseClassName(get_class($this->record))
            );
        }
    }


    /**
     * Sanitise a model class' name for inclusion in a link
     * @return string
     */
    protected function sanitiseClassName($class)
    {
        return str_replace('\\', '-', $class ?? '');
    }
}
