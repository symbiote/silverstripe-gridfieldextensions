<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\View\ArrayData;

/**
 * Displays a link to an external source referenced 'external link'
 */
class GridFieldExternalLink extends GridFieldDataColumns
{
    /**
     * Add a column for the actions
     *
     * @param type $gridField
     * @param array $columns
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns ?? [])) {
            $columns[] = 'Actions';
        }
    }

    /**
     * Return any special attributes that will be used for FormField::create_tag()
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'col-buttons'];
    }

    /**
     * Add the title
     *
     * @param GridField $gridField
     * @param string $columnName
     * @return array
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'Actions') {
            return ['title' => ''];
        }
        return [];
    }

    /**
     * Which columns are handled by this component
     *
     * @param type $gridField
     * @return type
     */
    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    /**
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     *
     * @return string - the HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        $data = ArrayData::create([
            'Link' => $record->hasMethod('getExternalLink') ? $record->getExternalLink() : $record->ExternalLink,
            'Text' => $record->hasMethod('getExternalLinkText') ? $record->getExternalLinkText() : 'External Link'
        ]);

        return $data->renderWith('GridFieldExternalLink');
    }
}
