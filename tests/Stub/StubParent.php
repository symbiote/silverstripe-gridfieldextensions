<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class StubParent extends DataObject implements TestOnly
{
    private static $has_many = array(
        'MyHasMany' => StubOrdered::class,
        'MyHasManySubclass' => StubSubclass::class
    );

    private static $many_many = array(
        'MyManyMany' => StubOrdered::class
    );

    private static $many_many_extraFields = array(
        'MyManyMany' => array('ManyManySort' => 'Int')
    );

    private static $table_name = 'StubParent';
}
