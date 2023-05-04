<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class TitleArraySortedObject extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar',
        'Iden' => 'Varchar',
        'OtherSort' => 'Int'
    ];

    private static array $default_sort = [
        'Title' => 'ASC',
        'OtherSort' => 'ASC',
    ];

    private static $table_name = 'TitleArraySortedObject';
}
