<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class TitleSortedObject extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar',
        'Iden' => 'Varchar',
        'DefaultSort' => 'Int'
    ];

    private static $default_sort = '"DefaultSort" ASC';

    private static $table_name = 'TitleSortedObject';
}
