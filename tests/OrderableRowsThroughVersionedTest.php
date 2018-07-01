<?php

namespace Symbiote\GridFieldExtensions\Tests;

use ReflectionMethod;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use Symbiote\GridFieldExtensions\Tests\Stub\ThroughDefiner;
use Symbiote\GridFieldExtensions\Tests\Stub\ThroughIntermediary;
use Symbiote\GridFieldExtensions\Tests\Stub\ThroughBelongs;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class OrderableRowsThroughTest extends SapphireTest
{
    protected static $fixture_file = 'OrderableRowsThroughTest.yml';

    protected static $extra_dataobjects = [
        ThroughDefiner::class,
        ThroughIntermediary::class,
        ThroughBelongs::class,
    ];

    protected static $required_extensions = [
        ThroughDefiner::class => [Versioned::class],
        ThroughIntermediary::class => [Versioned::class],
        ThroughBelongs::class => [Versioned::class],
    ];

    protected $originalReadingMode;

    protected function setUp()
    {
        parent::setUp();
        $this->orignalReadingMode = Versioned::get_reading_mode();
    }

    protected function tearDown()
    {
        Versioned::set_reading_mode($this->originalReadingMode);
        unset($this->originalReadingMode);
        parent::tearDown();
    }

    /**
     * Basically the same as GridFieldOrderableRowsTest::testReorderItems
     * but with some Versioned calls & checks mixed in.
     */
    public function testReorderingSavesAndPublishes()
    {
        $parent = $this->objFromFixture(ThroughDefiner::class, 'DefinerOne');
        $relationName = 'Belongings';
        $sortName = 'Sort';

        $orderable = new GridFieldOrderableRows($sortName);
        $reflection = new ReflectionMethod($orderable, 'executeReorder');
        $reflection->setAccessible(true);

        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        // This test data is versioned - ensure we're all published
        $parent->publishRecursive();
        // there should be no difference between stages at this point
        foreach ($parent->$relationName() as $item) {
            $this->assertFalse(
                $item->getJoin()->stagesDiffer(),
                'No records should be different from their published versions'
            );
        }

        $grid = new GridField(
            'Belongings',
            'Testing Many Many',
            $parent->$relationName()->sort($sortName),
            $config
        );

        $originalOrder = $parent->$relationName()->sort($sortName)->column('ID');
        // Ring (un)shift by one, e.g. 3,2,1 becomes 1,3,2.
        // then string key our new order starting at 1
        $desiredOrder = array_values($originalOrder);
        array_unshift($desiredOrder, array_pop($desiredOrder));
        $desiredOrder = array_combine(
            range('1', count($desiredOrder)),
            $desiredOrder
        );
        $this->assertNotEquals($originalOrder, $desiredOrder);

        // Perform the reorder
        $reflection->invoke($orderable, $grid, $desiredOrder);

        // Verify draft stage has reordered
        Versioned::set_stage(Versioned::DRAFT);
        $newOrder = $parent->$relationName()->sort($sortName)->map($sortName, 'ID')->toArray();
        $this->assertEquals($desiredOrder, $newOrder);

        // reorder should have been handled as versioned - there should be a difference between stages now
        // by using a ring style shift every item should have a new sort (thus a new version).
        $differenceFound = false;
        foreach ($parent->$relationName() as $item) {
            if ($item->getJoin()->stagesDiffer()) {
                $differenceFound = true;
            }
        }
        $this->assertTrue($differenceFound, 'All records should have changes in draft');
        
        // Verify live stage has NOT reordered
        Versioned::set_stage(Versioned::LIVE);
        $sameOrder = $parent->$relationName()->sort($sortName)->column('ID');
        $this->assertEquals($originalOrder, $sameOrder);

        $parent->publishRecursive();

        foreach ($parent->$relationName() as $item) {
            $this->assertFalse(
                $item->getJoin()->stagesDiffer(),
                'No records should be different from their published versions anymore'
            );
        }

        $newLiveOrder = $parent->$relationName()->sort($sortName)->map($sortName, 'ID')->toArray();
        $this->assertEquals($desiredOrder, $newLiveOrder);
    }
}
