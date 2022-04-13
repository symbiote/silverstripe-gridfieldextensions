<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub;

use SilverStripe\Dev\TestOnly;

class StubA implements TestOnly
{
    public function i18n_singular_name()
    {
        $class = get_class($this);
        return substr($class ?? '', -1);
    }

    public function canCreate()
    {
        return true;
    }
}
