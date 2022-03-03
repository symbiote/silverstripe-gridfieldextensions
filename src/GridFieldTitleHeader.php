<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

/**
 * A simple header which displays column titles.
 */
class GridFieldTitleHeader extends AbstractGridFieldComponent implements GridField_HTMLProvider
{

    public function getHTMLFragments($grid)
    {
        $cols = ArrayList::create();

        foreach ($grid->getColumns() as $name) {
            $meta = $grid->getColumnMetadata($name);

            $cols->push(ArrayData::create(array(
                'Name'  => $name,
                'Title' => $meta['title']
            )));
        }

        return array(
            'header' => $cols->renderWith(__CLASS__)
        );
    }
}
