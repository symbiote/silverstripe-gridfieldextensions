<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class StubParent extends DataObject implements TestOnly
{
    private static $has_many = [
        'MyHasMany' => StubOrdered::class,
        'MyHasManySubclass' => StubSubclass::class,
        'MyHasManySubclassOrderedVersioned' => StubSubclassOrderedVersioned::class,
    ];

    private static $many_many = [
        'MyManyMany' => StubOrdered::class,
    ];

    private static $many_many_extraFields = [
        'MyManyMany' => ['ManyManySort' => 'Int'],
    ];

    private static $table_name = 'StubParent';
}
