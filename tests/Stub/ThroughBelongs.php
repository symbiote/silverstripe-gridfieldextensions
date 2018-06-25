<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class ThroughBelongs extends DataObject implements TestOnly
{
    private static $table_name = 'BelongsThrough';
    
    private static $belongs_many_many = [
        'Definers' => ThroughDefiner::class,
    ];
}
