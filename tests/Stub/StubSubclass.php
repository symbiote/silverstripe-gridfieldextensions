<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class StubSubclass extends StubOrdered implements TestOnly
{
    private static $table_name = 'StubSubclass';
}
