<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\ORM\Search\SearchContext;

/**
 * Used by {@link GridFieldAddExistingSearchButton} to provide the searching
 * functionality.
 */
class GridFieldAddExistingSearchHandler extends RequestHandler
{

    private static $allowed_actions = array(
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

    public function __construct($grid, $button)
    {
        $this->grid    = $grid;
        $this->button  = $button;
        $this->context = singleton($grid->getModelClass())->getDefaultSearchContext();

        parent::__construct();
    }

    public function index()
    {
        return $this->renderWith(__CLASS__);
    }

    public function add($request)
    {
        if (!$id = $request->postVar('id')) {
            $this->httpError(400);
        }

        $list = $this->grid->getList();
        $item = DataList::create($list->dataClass())->byID($id);

        if (!$item) {
            $this->httpError(400);
        }

        $list->add($item);
    }

    /**
     * @return Form
     */
    public function SearchForm()
    {
        $form = Form::create(
            $this,
            'SearchForm',
            $this->context->getFields(),
            FieldList::create(
                FormAction::create('doSearch', _t('GridFieldExtensions.SEARCH', 'Search'))
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary font-icon-search')
            )
        );

        $form->addExtraClass('stacked add-existing-search-form form--no-dividers');
        $form->setFormMethod('GET');

        return $form;
    }

    public function doSearch($data, $form)
    {
        $list = $this->context->getQuery($data, false, null, $this->getSearchList());
        $list = $list->subtract($this->grid->getList());
        $list = PaginatedList::create($list, $this->request);

        $data = $this->customise(array(
            'SearchForm' => $form,
            'Items'      => $list
        ));
        return $data->index();
    }

    public function Items()
    {
        $list = $this->getSearchList();
        $list = $list->subtract($this->grid->getList());
        $list = PaginatedList::create($list, $this->request);

        return $list;
    }

    public function Link($action = null)
    {
        return Controller::join_links($this->grid->Link(), 'add-existing-search', $action);
    }

    /**
     * @return DataList
     */
    protected function getSearchList()
    {
        return $this->button->getSearchList() ?: DataList::create($this->grid->getList()->dataClass());
    }
}
