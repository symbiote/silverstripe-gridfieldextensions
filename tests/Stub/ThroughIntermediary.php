<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class ThroughIntermediary extends DataObject implements TestOnly
{
    private static $table_name = 'IntermediaryThrough';

    private static $db = [
        'Sort' => 'Int',
    ];

    private static $has_one = [
        'Defining' => ThroughDefiner::class,
        'Belonging' => ThroughBelongs::class,
    ];
}
