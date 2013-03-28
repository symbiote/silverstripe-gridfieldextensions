<?php
/**
 * Used by {@link GridFieldAddExistingSearchButton} to provide the searching
 * functionality.
 */
class GridFieldAddExistingSearchHandler extends RequestHandler {

	public static $allowed_actions = array(
		'index',
		'add',
		'SearchForm'
	);

	/**
	 * @var GridField
	 */
	protected $grid;

	/**
	 * @var GridFieldAddExistingSearchButton
	 */
	protected $button;

	/**
	 * @var SearchContext
	 */
	protected $context;

	public function __construct($grid, $button) {
		$this->grid    = $grid;
		$this->button  = $button;
		$this->context = singleton($grid->getModelClass())->getDefaultSearchContext();

		parent::__construct();
	}

	public function index() {
		return $this->renderWith('GridFieldAddExistingSearchHandler');
	}

	public function add($request) {
		if(!$id = $request->postVar('id')) {
			$this->httpError(400);
		}

		$list = $this->grid->getList();
		$item = DataList::create($list->dataClass())->byID($id);

		if(!$item) {
			$this->httpError(400);
		}

		$list->add($item);
	}

	/**
	 * @return Form
	 */
	public function SearchForm() {
		$form = new Form(
			$this,
			'SearchForm',
			$this->context->getFields(),
			new FieldList(
				FormAction::create('doSearch', _t('GridFieldExtensions.SEARCH', 'Search'))
					->setUseButtonTag(true)
					->addExtraClass('ss-ui-button')
					->setAttribute('data-icon', 'magnifier')
			)
		);

		$form->addExtraClass('stacked add-existing-search-form');
		$form->setFormMethod('GET');

		return $form;
	}

	public function doSearch($data, $form) {
		$list = $this->context->getResults($data);
		$list = $this->applySearchFilters($list);
		$list = $list->subtract($this->grid->getList());
		$list = new PaginatedList($list, $this->request);

		$data = $this->customise(array(
			'SearchForm' => $form,
			'Items'      => $list
		));
		return $data->index();
	}

	public function Items() {
		$list = DataList::create($this->grid->getList()->dataClass());
		$list = $this->applySearchFilters($list);
		$list = $list->subtract($this->grid->getList());
		$list = new PaginatedList($list, $this->request);

		return $list;
	}

	public function Link($action = null) {
		return Controller::join_links($this->grid->Link(), 'add-existing-search', $action);
	}
	
	public function applySearchFilters($list){
		$component	= $this->grid->getConfig()->getComponentByType('GridFieldAddExistingSearchButton');
	
		if($filters = $component->getSearchFilters())	{ $list = $list->filter($filters); }
		if($excludes = $component->getSearchExcludes())	{ $list = $list->exclude($excludes); }
	
		return $list;
	}

}
