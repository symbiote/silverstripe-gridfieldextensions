<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use Silverstripe\Dev\TestOnly;

class NamespacedClass implements TestOnly
{
    public function i18n_singular_name()
    {
        return 'NamespacedClass';
    }
    public function canCreate()
    {
        return true;
    }
}
