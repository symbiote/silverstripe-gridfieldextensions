<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

class StubOrderableChild extends StubUnorderable
{
    private static $db = [
        'Sort' => 'Int',
    ];

    private static $has_one = [
        'Parent' => StubOrdered::class,
    ];

    private static $default_sort = '"Sort" ASC';

    private static $table_name = 'StubOrderableChild';
}
