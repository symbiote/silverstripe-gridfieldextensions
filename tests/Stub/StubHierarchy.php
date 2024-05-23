<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Hierarchy\Hierarchy;

class StubHierarchy extends DataObject implements TestOnly
{
    private static $table_name = 'StubHierarchy';
    
    private static $extensions = [
        Hierarchy::class
    ];
    
    private static $db = [
        'Title' => 'Varchar'
    ];
}
