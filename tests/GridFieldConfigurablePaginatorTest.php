<?php

namespace Symbiote\GridFieldExtensions\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\ArrayList;
use Symbiote\GridFieldExtensions\GridFieldConfigurablePaginator;

class GridFieldConfigurablePaginatorTest extends SapphireTest
{
    /**
     * @var GridField
     */
    protected $gridField;

    public function setUp()
    {
        parent::setUp();

        // Some dummy GridField list data
        $data = ArrayList::create();
        for ($i = 1; $i <= 130; $i++) {
            $data->push(array('ID' => $i));
        }

        $this->gridField = GridField::create('Mock', null, $data);
    }

    public function testGetTotalRecords()
    {
        $paginator = new GridFieldConfigurablePaginator;
        $paginator->setGridField($this->gridField);

        $this->assertSame(130, $paginator->getTotalRecords());
    }

    public function testGetFirstShown()
    {
        $paginator = new GridFieldConfigurablePaginator;
        $paginator->setGridField($this->gridField);

        // No state
        $this->assertSame(1, $paginator->getFirstShown());

        // With a state
        $paginator->setFirstShown(123);
        $this->assertSame(123, $paginator->getFirstShown());

        // Too high!
        $paginator->setFirstShown(234);
        $this->assertSame(1, $paginator->getFirstShown());
    }

    public function testGetLastShown()
    {
        $paginator = new GridFieldConfigurablePaginator(20, array(10, 20, 30));
        $paginator->setGridField($this->gridField);

        $this->assertSame(20, $paginator->getLastShown());

        $paginator->setFirstShown(5);
        $this->assertSame(24, $paginator->getLastShown());
    }

    public function testGetTotalPages()
    {
        $paginator = new GridFieldConfigurablePaginator(20, array(20, 40, 60));
        $paginator->setGridField($this->gridField);

        // Default calculation
        $this->assertSame(7, $paginator->getTotalPages());

        // With a standard "first shown" record number, e.g. page 2
        $paginator->setFirstShown(21);
        $this->assertSame(7, $paginator->getTotalPages());

        // Non-standard "first shown", e.g. when a page size is changed at page 3. In this case the first page is
        // 20 records, the second page is 7 records, third page 20 records, etc
        $paginator->setFirstShown(27);
        $this->assertSame(8, $paginator->getTotalPages());

        // ... and when the page size has also been changed. In this case the first page is 57 records, second page
        // 60 records and last page is 13 records
        $paginator->setFirstShown(57);
        $paginator->setItemsPerPage(60);
        $this->assertSame(3, $paginator->getTotalPages());
    }

    public function testItemsPerPageIsSetToFirstInPageSizesListWhenChanged()
    {
        $paginator = new GridFieldConfigurablePaginator(20, array(20, 40, 60));
        $paginator->setGridField($this->gridField);

        // Initial state, should be what was provided to the constructor
        $this->assertSame(20, $paginator->getItemsPerPage());

        $paginator->setPageSizes(array(50, 100, 200));

        // Set via public API, should now be set to 50
        $this->assertSame(50, $paginator->getItemsPerPage());
    }

    public function testGetCurrentPreviousAndNextPages()
    {
        $paginator = new GridFieldConfigurablePaginator(20, array(20, 40, 60));
        $paginator->setGridField($this->gridField);

        // No page selected (first page)
        $this->assertSame(1, $paginator->getCurrentPage());
        $this->assertSame(1, $paginator->getPreviousPage());
        $this->assertSame(2, $paginator->getNextPage());

        // Second page
        $paginator->setFirstShown(21);
        $this->assertSame(2, $paginator->getCurrentPage());
        $this->assertSame(1, $paginator->getPreviousPage());
        $this->assertSame(3, $paginator->getNextPage());

        // Third page
        $paginator->setFirstShown(41);
        $this->assertSame(3, $paginator->getCurrentPage());
        $this->assertSame(2, $paginator->getPreviousPage());
        $this->assertSame(4, $paginator->getNextPage());

        // Fourth page, partial record count
        $paginator->setFirstShown(42);
        $this->assertSame(4, $paginator->getCurrentPage());
        $this->assertSame(3, $paginator->getPreviousPage());
        $this->assertSame(5, $paginator->getNextPage());

        // Last page (default paging)
        $paginator->setFirstShown(121);
        $this->assertSame(7, $paginator->getCurrentPage());
        $this->assertSame(6, $paginator->getPreviousPage());
        $this->assertSame(7, $paginator->getNextPage());

        // Non-standard page size should recalculate the page numbers to be relative to the page size
        $paginator->setFirstShown(121);
        $paginator->setItemsPerPage(60);
        $this->assertSame(3, $paginator->getCurrentPage());
        $this->assertSame(2, $paginator->getPreviousPage());
        $this->assertSame(3, $paginator->getNextPage());
    }

    public function testPageSizesAreConfigurable()
    {
        // Via constructor
        $paginator = new GridFieldConfigurablePaginator(3, array(2, 4, 6));
        $this->assertSame(3, $paginator->getItemsPerPage());
        $this->assertSame(array(2, 4, 6), $paginator->getPageSizes());

        // Via public API
        $paginator->setPageSizes(array(10, 20, 30));
        $this->assertSame(array(10, 20, 30), $paginator->getPageSizes());

        // Via default configuration
        $paginator = new GridFieldConfigurablePaginator;
        $default = Config::inst()->get(GridFieldConfigurablePaginator::class, 'default_page_sizes');
        $this->assertSame($default, $paginator->getPageSizes());
    }

    public function testGetPageSizesAsList()
    {
        $paginator = new GridFieldConfigurablePaginator(10, array(10, 20, 30));
        $this->assertListEquals(array(
            array('Size' => '10', 'Selected' => true),
            array('Size' => '20', 'Selected' => false),
            array('Size' => '30', 'Selected' => false),
        ), $paginator->getPageSizesAsList());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage No GridField available yet for this request!
     */
    public function testGetGridFieldThrowsExceptionWhenNotSet()
    {
        $paginator = new GridFieldConfigurablePaginator;
        $paginator->getGridField();
    }

    public function testGetPagerActions()
    {
        $controls = array(
            'prev' => array(
                'title' => 'Previous',
                'args' => array(
                    'next-page' => 123,
                    'first-shown' => 234
                ),
                'extra-class' => 'ss-gridfield-previouspage',
                'disable-previous' => false
            ),
            'next' => array(
                'title' => 'Next',
                'args' => array(
                    'next-page' => 234,
                    'first-shown' => 123
                ),
                'extra-class' => 'ss-gridfield-nextpage',
                'disable-next' => true
            )
        );

        $gridField = $this->getMockBuilder(GridField::class)->disableOriginalConstructor()->getMock();
        $paginator = new GridFieldConfigurablePaginator;
        $result = $paginator->getPagerActions($controls, $gridField);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('next', $result);
        $this->assertContainsOnlyInstancesOf(GridField_FormAction::class, $result);

        $this->assertFalse($result['prev']->isDisabled());

        $this->assertTrue((bool) $result['next']->hasClass('ss-gridfield-nextpage'));
        $this->assertTrue($result['next']->isDisabled());
    }

    public function testSinglePageWithLotsOfItems()
    {
        $paginator = new GridFieldConfigurablePaginator(null, array(100, 200, 300));
        $this->assertSame(100, $paginator->getItemsPerPage());
    }

    /**
     * Set something to the GridField's paginator state data
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    protected function setState($key, $value)
    {
        $this->gridField->State->GridFieldConfigurablePaginator->$key = $value;
        return $this;
    }
}
