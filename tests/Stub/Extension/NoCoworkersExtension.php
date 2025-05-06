<?php

namespace Symbiote\GridFieldExtensions\Tests\Stub\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * Removes 'ColleagueNames' field from summary fields so we can accurately tell which records are being displayed
 */
class NoCoworkersExtension extends Extension implements TestOnly
{
    protected function updateSummaryFields(array &$fields): void
    {
        unset($fields['ColleagueNames']);
    }
}
