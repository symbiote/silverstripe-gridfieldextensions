<?php

namespace Symbiote\GridFieldExtensions\Tests;

use ReflectionMethod;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\GridFieldExtensions\Tests\Stub\StubOrdered;
use Symbiote\GridFieldExtensions\Tests\Stub\StubParent;
use Symbiote\GridFieldExtensions\Tests\Stub\StubSubclass;

/**
 * Tests for the {@link GridFieldOrderableRows} component.
 */
class GridFieldOrderableRowsTest extends SapphireTest
{

    protected $usesDatabase = true;

    protected static $fixture_file = 'GridFieldOrderableRowsTest.yml';

    protected static $extra_dataobjects = array(
        StubParent::class,
        StubOrdered::class,
        StubSubclass::class,
    );

    public function testReorderItems()
    {
        $orderable = new GridFieldOrderableRows('ManyManySort');
        $reflection = new ReflectionMethod($orderable, 'executeReorder');
        $reflection->setAccessible(true);

        $parent = $this->objFromFixture(StubParent::class, 'parent');

        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        $grid = new GridField(
            'MyManyMany',
            'My Many Many',
            $parent->MyManyMany()->sort('ManyManySort'),
            $config
        );

        $originalOrder = $parent->MyManyMany()->sort('ManyManySort')->column('ID');
        $desiredOrder = array();

        // Make order non-contiguous, and 1-based
        foreach (array_reverse($originalOrder) as $index => $id) {
            $desiredOrder[$index * 2 + 1] = $id;
        }

        $this->assertNotEquals($originalOrder, $desiredOrder);

        $reflection->invoke($orderable, $grid, $desiredOrder);

        $newOrder = $parent->MyManyMany()->sort('ManyManySort')->map('ManyManySort', 'ID')->toArray();

        $this->assertEquals($desiredOrder, $newOrder);
    }

    /**
     * @covers GridFieldOrderableRows::getSortTable
     */
    public function testGetSortTable()
    {
        $orderable = new GridFieldOrderableRows();

        $parent = new StubParent();
        $parent->write();

        $this->assertEquals(
            'StubOrdered',
            $orderable->getSortTable($parent->MyHasMany())
        );

        $this->assertEquals(
            'StubOrdered',
            $orderable->getSortTable($parent->MyHasManySubclass())
        );

        $this->assertEquals(
            'StubOrdered',
            $orderable->getSortTable($parent->MyManyMany())
        );

        $this->assertEquals(
            'StubParent_MyManyMany',
            $orderable->setSortField('ManyManySort')->getSortTable($parent->MyManyMany())
        );
    }
}
