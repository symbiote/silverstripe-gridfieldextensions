<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\View\Requirements;

/**
 * Utility functions for the grid fields extension module.
 */
class GridFieldExtensions
{
    public static function include_requirements()
    {
        Requirements::css('symbiote/silverstripe-gridfieldextensions:css/GridFieldExtensions.css');
        Requirements::javascript('symbiote/silverstripe-gridfieldextensions:javascript/GridFieldExtensions.js');
    }
}
