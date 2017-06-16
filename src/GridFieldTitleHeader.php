<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

/**
 * A simple header which displays column titles.
 */
class GridFieldTitleHeader implements GridField_HTMLProvider
{

    public function getHTMLFragments($grid)
    {
        $cols = new ArrayList();

        foreach ($grid->getColumns() as $name) {
            $meta = $grid->getColumnMetadata($name);

            $cols->push(new ArrayData(array(
                'Name'  => $name,
                'Title' => $meta['title']
            )));
        }

        return array(
            'header' => $cols->renderWith(__CLASS__)
        );
    }
}
