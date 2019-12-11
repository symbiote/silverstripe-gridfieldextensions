<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class PolymorphM2MParent extends DataObject implements TestOnly
{
    private static $table_name = 'TableOnly_PolymorphM2MParent';

    private static $many_many = [
        'Children' => [
            'through' => PolymorphM2MMapper::class,
            'from' => 'Parent',
            'to' => 'Child',
        ]
    ];
}
