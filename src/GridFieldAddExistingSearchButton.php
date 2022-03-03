<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;

/**
 * A modal search dialog which uses search context to search for and add
 * existing records to a grid field.
 */
class GridFieldAddExistingSearchButton extends AbstractGridFieldComponent implements
    GridField_HTMLProvider,
    GridField_URLHandler
{

    private static $allowed_actions = array(
        'handleSearch'
    );

    protected $title;
    protected $fragment;
    protected $searchList;

    /**
     * @param string $fragment
     */
    public function __construct($fragment = 'buttons-before-left')
    {
        $this->fragment = $fragment;
        $this->title    = _t('GridFieldExtensions.ADDEXISTING', 'Add Existing');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return GridFieldAddExistingSearchButton $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     * @return GridFieldAddExistingSearchButton $this
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * Sets a custom list to use to provide the searchable items.
     *
     * @param SS_List $list
     * @return GridFieldAddExistingSearchButton $this
     */
    public function setSearchList(SS_List $list)
    {
        $this->searchList = $list;
        return $this;
    }

    /**
     * @return SS_List|null
     */
    public function getSearchList()
    {
        return $this->searchList;
    }

    public function getHTMLFragments($grid)
    {
        GridFieldExtensions::include_requirements();

        $data = ArrayData::create([
            'Title' => $this->getTitle(),
            'Classes' => 'action btn btn-primary font-icon-search add-existing-search',
            'Link' => $grid->Link('add-existing-search'),
        ]);

        return [
            $this->fragment => $data->renderWith(__CLASS__),
        ];
    }

    public function getURLHandlers($grid)
    {
        return array(
            'add-existing-search' => 'handleSearch'
        );
    }

    public function handleSearch($grid, $request)
    {
        return GridFieldAddExistingSearchHandler::create($grid, $this);
    }
}
