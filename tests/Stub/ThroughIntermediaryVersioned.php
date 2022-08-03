<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * @property int $DefiningID
 * @property int $BelongingID
 * @method ThroughDefinerVersioned Defining()
 * @method ThroughBelongsVersioned Belonging()
 * @mixin Versioned
 */
class ThroughIntermediaryVersioned extends DataObject implements TestOnly
{
    private static string $table_name = 'ThroughIntermediaryVersioned';

    private static array $db = [
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Defining' => ThroughDefinerVersioned::class,
        'Belonging' => ThroughBelongsVersioned::class,
    ];

    private static array $extensions = [
        Versioned::class,
    ];
}
