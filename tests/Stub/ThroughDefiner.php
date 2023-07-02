<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class ThroughDefiner extends DataObject implements TestOnly
{
    private static $table_name = 'ManyThrough';

    private static $many_many = [
        'Belongings' => [
            'through' => ThroughIntermediary::class,
            'from' => 'Defining',
            'to' => 'Belonging',
        ]
    ];

    private static $owns = [
        'Belongings'
    ];
}
