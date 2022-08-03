<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\Versioned\Versioned;

/**
 * @method ManyManyThroughList|ThroughIntermediaryVersioned Belongings()
 * @mixin Versioned
 */
class ThroughDefinerVersioned extends DataObject implements TestOnly
{
    private static string $table_name = 'ThroughDefinerVersioned';

    private static array $many_many = [
        'Belongings' => [
            'through' => ThroughIntermediaryVersioned::class,
            'from' => 'Defining',
            'to' => 'Belonging',
        ]
    ];

    private static array $owns = [
        'Belongings'
    ];

    private static array $extensions = [
        Versioned::class,
    ];
}
