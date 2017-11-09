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

        $fields->addFieldsToTab('Root.Apple', TextField::create('Apple'));
        $fields->addFieldsToTab('Root.Orange', TextField::create('Orange'));

        return $fields;
    }
}
