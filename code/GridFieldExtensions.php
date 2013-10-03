<?php
/**
 * Utility functions for the grid fields extension module.
 */
class GridFieldExtensions {

	public static function include_requirements() {
		Requirements::css('gridfieldextensions/css/GridFieldExtensions.css');
		Requirements::javascript('gridfieldextensions/javascript/GridFieldExtensions.js');
	}

}
