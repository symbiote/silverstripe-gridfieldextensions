<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use Symbiote\GridFieldExtensions\GridFieldConfigurablePaginator;

class ConfigurablePaginatorExtension extends Extension implements TestOnly
{
    protected function updateCMSFields(FieldList $fields): void
    {
        /** @var GridField $gridField */
        $gridField = $fields->dataFieldByName('Employees');
        $config = $gridField->getConfig();
        $config->removeComponentsByType(GridFieldPaginator::class);
        $config->addComponent(new GridFieldConfigurablePaginator());
    }
}
