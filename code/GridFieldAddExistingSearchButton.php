<?php
/**
 * A modal search dialog which uses search context to search for and add
 * existing records to a grid field.
 */
class GridFieldAddExistingSearchButton implements
	GridField_HTMLProvider,
	GridField_URLHandler {

	protected $fragment;

	/**
	 * @param string $fragment
	 */
	public function __construct($fragment = 'before') {
		$this->fragment = $fragment;
	}

	public function getHTMLFragments($grid) {
		Requirements::css('gridfieldextensions/css/GridFieldExtensions.css');
		Requirements::javascript('gridfieldextensions/javascript/GridFieldExtensions.js');

		$data = new ArrayData(array(
			'Link'  => $grid->Link('add-existing-search')
		));

		return array(
			$this->fragment => $data->renderWith('GridFieldAddExistingSearchButton'),
		);
	}

	public function getURLHandlers($grid) {
		return array(
			'add-existing-search' => 'handleSearch'
		);
	}

	public function handleSearch($grid, $request) {
		return new GridFieldAddExistingSearchHandler($grid, $this);
	}

}
