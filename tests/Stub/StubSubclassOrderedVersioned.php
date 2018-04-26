<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Class StubOrderedVersioned
 * @package Symbiote\GridFieldExtensions\Tests\Stub
 */
class StubSubclassOrderedVersioned extends StubOrderedVersioned
{
    /**
     * @var string
     */
    private static $table_name = 'StubSubclassOrderedVersioned';

    /**
     * @var array
     */
    private static $db = [
        'ExtraField' => 'Int',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Parent' => StubParent::class,
    ];
}
