<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class StubOrdered extends DataObject implements TestOnly
{
    private static $db = [
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'Parent' => StubParent::class
    ];

    private static $has_many = [
        'Children' => StubOrderableChild::class,
    ];

    private static $belongs_many_many = [
        'MyManyMany' => StubParent::class,
    ];

    private static $table_name = 'StubOrdered';
}
