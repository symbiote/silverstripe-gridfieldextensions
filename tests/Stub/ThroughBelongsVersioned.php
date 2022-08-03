<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;

/**
 * @method ManyManyList|ThroughDefinerVersioned[] Definers()
 * @mixin Versioned
 */
class ThroughBelongsVersioned extends DataObject implements TestOnly
{
    private static string $table_name = 'ThroughBelongsVersioned';

    private static array $belongs_many_many = [
        'Definers' => ThroughDefinerVersioned::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];
}
