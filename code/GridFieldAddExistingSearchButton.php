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
	protected $searchList;

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

	/**
	 * Sets a custom list to use to provide the searchable items.
	 *
	 * @param SS_List $list
	 */
	public function setSearchList(SS_List $list) {
		$this->searchList = $list;
	}

	/**
	 * @return SS_List|null
	 */
	public function getSearchList() {
		return $this->searchList;
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

}
