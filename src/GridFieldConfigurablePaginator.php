<?php

namespace Symbiote\GridFieldExtensions;

use Exception;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridState_Data;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\Limitable;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\UnsavedRelationList;
use SilverStripe\View\ArrayData;

/**
 * GridFieldConfigurablePaginator paginates the {@link GridField} list and adds controls to the bottom of
 * the {@link GridField}. The page sizes are configurable.
 */
class GridFieldConfigurablePaginator extends GridFieldPaginator
{
    use Configurable;

    /**
     * Specifies default page sizes
     *
     * @config
     * @var int
     */
    private static $default_page_sizes = array(15, 30, 60);

    /**
     * @var GridField
     */
    protected $gridField;

    /**
     * @var GridState_Data
     */
    protected $gridFieldState;

    /**
     * @var int[]
     */
    protected $pageSizes = array();

    /**
     * @param int $itemsPerPage  How many items should be displayed per page
     * @param int $pageSizes The page sizes to show in the dropdown
     */
    public function __construct($itemsPerPage = null, $pageSizes = null)
    {
        $this->setPageSizes($pageSizes ?: $this->config()->get('default_page_sizes'));

        if (!$itemsPerPage) {
            $itemsPerPage = $this->pageSizes[0];
        }

        parent::__construct($itemsPerPage);
    }

    /**
     * Get the total number of records in the list
     *
     * @return int
     */
    public function getTotalRecords()
    {
        return (int) $this->getGridField()->getList()->count();
    }

    /**
     * Get the first shown record number
     *
     * @return int
     */
    public function getFirstShown()
    {
        $firstShown = $this->getGridPagerState()->firstShown ?: 1;
        // Prevent visiting a page with an offset higher than the total number of items
        if ($firstShown > $this->getTotalRecords()) {
            $this->getGridPagerState()->firstShown = $firstShown = 1;
        }
        return $firstShown;
    }

    /**
     * Set the first shown record number. Will be stored in the state.
     *
     * @param  int $firstShown
     * @return $this
     */
    public function setFirstShown($firstShown = 1)
    {
        $this->getGridPagerState()->firstShown = (int) $firstShown;
        return $this;
    }

    /**
     * Get the last shown record number
     *
     * @return int
     */
    public function getLastShown()
    {
        return min($this->getTotalRecords(), $this->getFirstShown() + $this->getItemsPerPage() - 1);
    }

    /**
     * Get the total number of pages, given the current number of items per page. The total
     * pages might be higher than <totalitems> / <itemsperpage> if the first shown record
     * is half way through a standard page break point.
     *
     * @return int
     */
    public function getTotalPages()
    {
        // Pages before
        $pages = ceil(($this->getFirstShown() - 1) / $this->getItemsPerPage());

        // Current page
        $pages++;

        // Pages after
        $pages += ceil(($this->getTotalRecords() - $this->getLastShown()) / $this->getItemsPerPage());

        return (int) $pages;
    }


    /**
     * Get the page currently active. This is calculated by adding one to the previous number
     * of pages calculated via the "first shown record" position.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return (int) ceil(($this->getFirstShown() - 1) / $this->getItemsPerPage()) + 1;
    }

    /**
     * Get the next page number
     *
     * @return int
     */
    public function getNextPage()
    {
        return min($this->getTotalPages(), $this->getCurrentPage() + 1);
    }

    /**
     * Get the previous page number
     *
     * @return int
     */
    public function getPreviousPage()
    {
        return max(1, $this->getCurrentPage() - 1);
    }

    /**
     * Set the page sizes to use in the "Show x" dropdown
     *
     * @param array $pageSizes
     * @return $this
     */
    public function setPageSizes(array $pageSizes)
    {
        $this->pageSizes = $pageSizes;

        // Reset items per page
        $this->setItemsPerPage(current($pageSizes ?? []));

        return $this;
    }

    /**
     * Get the sizes for the "Show x" dropdown
     *
     * @return array
     */
    public function getPageSizes()
    {
        return $this->pageSizes;
    }

    /**
     * Gets a list of page sizes for use in templates as a dropdown
     *
     * @return ArrayList
     */
    public function getPageSizesAsList()
    {
        $pageSizes = ArrayList::create();
        $perPage = $this->getItemsPerPage();
        foreach ($this->getPageSizes() as $pageSize) {
            $pageSizes->push(array(
                'Size' => $pageSize,
                'Selected' => $pageSize == $perPage
            ));
        }
        return $pageSizes;
    }

    /**
     * Get the GridField used in this request
     *
     * @return GridField
     * @throws Exception If the GridField has not been previously set
     */
    public function getGridField()
    {
        if ($this->gridField) {
            return $this->gridField;
        }
        throw new Exception('No GridField available yet for this request!');
    }

    /**
     * Set the GridField so it can be used in other parts of the component during this request
     *
     * @param GridField $gridField
     * @return $this
     */
    public function setGridField(GridField $gridField)
    {
        $this->gridField = $gridField;
        return $this;
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        $this->setGridField($gridField);

        if ($actionName !== 'paginate') {
            return;
        }
        $state = $this->getGridPagerState();

        $state->firstShown = (int) $arguments['first-shown'];
        $state->pageSize = $data[$gridField->getName()]['page-sizes'];

        $this->setItemsPerPage($state->pageSize);
    }

    public function getManipulatedData(GridField $gridField, SS_List $dataList)
    {
        // Assign the GridField to the class so it can be used later in the request
        $this->setGridField($gridField);

        // Retain page sizes during actions provided by other components
        $state = $this->getGridPagerState();
        if (is_numeric($state->pageSize)) {
            $this->setItemsPerPage($state->pageSize);
        }

        if (!($dataList instanceof Limitable) || ($dataList instanceof UnsavedRelationList)) {
            return $dataList;
        }

        return $dataList->limit($this->getItemsPerPage(), $this->getFirstShown() - 1);
    }

    /**
     * Add the configurable page size options to the template data
     *
     * {@inheritDoc}
     *
     * @param  GridField $gridField
     * @return ArrayData|null
     */
    public function getTemplateParameters(GridField $gridField)
    {
        $state = $this->getGridPagerState();
        if (is_numeric($state->pageSize)) {
            $this->setItemsPerPage($state->pageSize);
        }
        $arguments = $this->getPagerArguments();

        // Figure out which page and record range we're on
        if (!$arguments['total-rows']) {
            return null;
        }

        // Define a list of the FormActions that should be generated for pager controls (see getPagerActions())
        $controls = array(
            'first' => array(
                'title' => 'First',
                'args' => array('first-shown' => 1),
                'extra-class' => 'btn btn-secondary btn--hide-text btn-sm font-icon-angle-double-left '
                    . 'ss-gridfield-pagination-action ss-gridfield-firstpage',
                'disable-previous' => ($this->getCurrentPage() == 1)
            ),
            'prev' => array(
                'title' => 'Previous',
                'args' => array('first-shown' => $arguments['first-shown'] - $this->getItemsPerPage()),
                'extra-class' => 'btn btn-secondary btn--hide-text btn-sm font-icon-angle-left '
                    . 'ss-gridfield-pagination-action ss-gridfield-previouspage',
                'disable-previous' => ($this->getCurrentPage() == 1)
            ),
            'next' => array(
                'title' => 'Next',
                'args' => array('first-shown' => $arguments['first-shown'] + $this->getItemsPerPage()),
                'extra-class' => 'btn btn-secondary btn--hide-text btn-sm font-icon-angle-right '
                .'ss-gridfield-pagination-action  ss-gridfield-nextpage',
                'disable-next' => ($this->getCurrentPage() == $arguments['total-pages'])
            ),
            'last' => array(
                'title' => 'Last',
                'args' => array('first-shown' => ($this->getTotalPages() - 1) * $this->getItemsPerPage() + 1),
                'extra-class' => 'btn btn-secondary btn--hide-text btn-sm font-icon-angle-double-right '
                    . 'ss-gridfield-pagination-action ss-gridfield-lastpage',
                'disable-next' => ($this->getCurrentPage() == $arguments['total-pages'])
            ),
            'pagesize' => array(
                'title' => 'Page Size',
                'args' => array('first-shown' => $arguments['first-shown']),
                'extra-class' => 'ss-gridfield-pagination-action ss-gridfield-pagesize-submit'
            ),
        );

        if ($controls['prev']['args']['first-shown'] < 1) {
            $controls['prev']['args']['first-shown'] = 1;
        }

        $actions = $this->getPagerActions($controls, $gridField);

        // Render in template
        return ArrayData::create(array(
            'OnlyOnePage' => ($arguments['total-pages'] == 1),
            'FirstPage' => $actions['first'],
            'PreviousPage' => $actions['prev'],
            'NextPage' => $actions['next'],
            'LastPage' => $actions['last'],
            'PageSizesSubmit' => $actions['pagesize'],
            'CurrentPageNum' => $this->getCurrentPage(),
            'NumPages' => $arguments['total-pages'],
            'FirstShownRecord' => $arguments['first-shown'],
            'LastShownRecord' => $arguments['last-shown'],
            'NumRecords' => $arguments['total-rows'],
            'PageSizes' => $this->getPageSizesAsList(),
            'PageSizesName' => $gridField->getName() . '[page-sizes]',
        ));
    }

    public function getHTMLFragments($gridField)
    {
        GridFieldExtensions::include_requirements();

        $gridField->addExtraClass('ss-gridfield-configurable-paginator');

        $forTemplate = $this->getTemplateParameters($gridField);
        if ($forTemplate) {
            return array(
                'footer' => $forTemplate->renderWith(
                    __CLASS__,
                    array('Colspan' => count($gridField->getColumns() ?? []))
                )
            );
        }
    }

    /**
     * Returns an array containing the arguments for the pagination: total rows, pages, first record etc
     *
     * @return array
     */
    protected function getPagerArguments()
    {
        return array(
            'total-rows' => $this->getTotalRecords(),
            'total-pages' => $this->getTotalPages(),
            'items-per-page' => $this->getItemsPerPage(),
            'first-shown' => $this->getFirstShown(),
            'last-shown' => $this->getLastShown(),
        );
    }

    /**
     * Returns FormActions for each of the pagination actions, in an array
     *
     * @param  array     $controls
     * @param  GridField $gridField
     * @return GridField_FormAction[]
     */
    public function getPagerActions(array $controls, GridField $gridField)
    {
        $actions = array();

        foreach ($controls as $key => $arguments) {
            $action = GridField_FormAction::create(
                $gridField,
                'pagination_' . $key,
                $arguments['title'],
                'paginate',
                $arguments['args']
            );

            if (isset($arguments['extra-class'])) {
                $action->addExtraClass($arguments['extra-class']);
            }

            if (isset($arguments['disable-previous']) && $arguments['disable-previous']) {
                $action = $action->performDisabledTransformation();
            } elseif (isset($arguments['disable-next']) && $arguments['disable-next']) {
                $action = $action->performDisabledTransformation();
            }

            $actions[$key] = $action;
        }

        return $actions;
    }

    public function getActions($gridField)
    {
        return array('paginate');
    }

    /**
     * Gets the state from the current request's GridField and sets some default values on it
     *
     * @param  GridField $gridField Not used, but present for parent method compatibility
     * @return GridState_Data
     */
    protected function getGridPagerState(GridField $gridField = null)
    {
        if (!$this->gridFieldState) {
            $state = $this->getGridField()->State->GridFieldConfigurablePaginator;

            // SS 3.1 compatibility (missing __call)
            if (is_object($state->firstShown)) {
                $state->firstShown = 1;
            }
            if (is_object($state->pageSize)) {
                $state->pageSize = $this->getItemsPerPage();
            }

            // Handle free input in the page number field
            $parentState = $this->getGridField()->State->GridFieldPaginator;
            if (is_object($parentState->currentPage)) {
                $parentState->currentPage = 0;
            }

            if ($parentState->currentPage >= 1) {
                $state->firstShown = ($parentState->currentPage - 1) * $this->getItemsPerPage() + 1;
                $parentState->currentPage = null;
            }

            $this->gridFieldState = $state;
        }

        return $this->gridFieldState;
    }
}
