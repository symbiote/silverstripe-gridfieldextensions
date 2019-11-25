<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Class StubOrderedVersioned
 * @package Symbiote\GridFieldExtensions\Tests\Stub
 */
class StubOrderedVersioned extends DataObject implements TestOnly
{
    /**
     * @var string
     */
    private static $table_name = 'StubOrderedVersioned';

    /**
     * @var array
     */
    private static $extensions = [
        Versioned::class,
    ];

    /**
     * @var array
     */
    private static $db = [
        'Sort' => 'Int',
    ];
}
