<?php
/**
 * Utility functions for the grid fields extension module.
 */
class GridFieldExtensions {

	public static function include_requirements() {
		$moduleDir = self::get_module_dir();
		Requirements::css($moduleDir.'/css/GridFieldExtensions.css');
		Requirements::javascript($moduleDir.'/javascript/GridFieldExtensions.js');
	}

	public static function get_module_dir() {
		return basename(dirname(__DIR__));
	}

}
