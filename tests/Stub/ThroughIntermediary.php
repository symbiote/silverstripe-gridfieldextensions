<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\Dev\TestOnly;

class ThroughIntermediary extends DataObject implements TestOnly
{
    private static $table_name = 'IntermediaryThrough';

    private static $db = [
        'Sort' => DBInt::class,
    ];
    
    private static $has_one = [
        'Defining' => ThroughDefiner::class,
        'Belonging' => ThroughBelongs::class,
    ];
}
