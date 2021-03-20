<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class StubUnorderable extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $table_name = 'StubUnorderable';

    private $canEdit = false;

    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
    }

    public function canEdit($member = null)
    {
        return $this->canEdit;
    }
}
