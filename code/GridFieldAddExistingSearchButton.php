<?php
/**
 * A modal search dialog which uses search context to search for and add
 * existing records to a grid field.
 */
class GridFieldAddExistingSearchButton implements
	GridField_HTMLProvider,
	GridField_URLHandler {

	protected $title;
	protected $fragment;
	protected $searchFilters	= null;
	protected $searchExcludes	= null;

	/**
	 * @param string $fragment
	 */
	public function __construct($fragment = 'buttons-before-left') {
		$this->fragment = $fragment;
		$this->title    = _t('GridFieldExtensions.ADDEXISTING', 'Add Existing');
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * @param string $fragment
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
	}

	public function getHTMLFragments($grid) {
		GridFieldExtensions::include_requirements();

		$data = new ArrayData(array(
			'Title' => $this->getTitle(),
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
	
	public function setSearchFilters($filters) {
		$this->searchFilters = $filters;
	}
	
	public function setSearchExcludes($excludes) {
		$this->searchExcludes = $excludes;
	}
	
	public function getSearchFilters() {
		return $this->searchFilters;
	}
	
	public function getSearchExcludes() {
		return $this->searchExcludes;
	}

}
