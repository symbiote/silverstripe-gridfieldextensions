<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class PolymorphM2MChild extends DataObject implements TestOnly
{
    private static $table_name = 'TestOnly_PolymorphM2MChild';

    private static $has_many = [
        'Parents' => PolymorphM2MMapper::class
    ];
}
