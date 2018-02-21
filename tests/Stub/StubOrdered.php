<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class StubOrdered extends DataObject implements TestOnly
{
    private static $db = array(
        'Sort' => 'Int'
    );

    private static $has_one = array(
        'Parent' => StubParent::class
    );

    private static $has_many = array(
        'Children' => StubOrderableChild::class,
    );

    private static $belongs_many_many =array(
        'MyManyMany' => StubParent::class,
    );

    private static $table_name = 'StubOrdered';
}
