<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class PolymorphM2MMapper extends DataObject implements TestOnly
{
    private static $table_name = 'TestOnly_PolymorphM2MMapper';

    private static $db = [
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'Parent' => DataObject::class, // PolymorphM2MParent
        'Child' => PolymorphM2MChild::class,
    ];

    private static $default_sort = '"Sort" ASC';
}
