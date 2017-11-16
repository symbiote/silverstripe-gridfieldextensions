<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;

class ClassWithTabs extends DataObject implements TestOnly
{
    private static $table_name = 'Stub_ClassWithTabs';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // ADD two new root level tabs - don't forget 'Main' is included by default!
        $fields->addFieldsToTab('Root.Apple', TextField::create('Apple'));
        $fields->addFieldsToTab('Root.Orange', TextField::create('Orange'));

        return $fields;
    }
}
