<?php

namespace Symbiote\GridFieldExtensions\Tests;

use ReflectionMethod;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\GridFieldExtensions\Tests\Stub\PolymorphM2MChild;
use Symbiote\GridFieldExtensions\Tests\Stub\PolymorphM2MMapper;
use Symbiote\GridFieldExtensions\Tests\Stub\PolymorphM2MParent;
use Symbiote\GridFieldExtensions\Tests\Stub\StubOrderableChild;
use Symbiote\GridFieldExtensions\Tests\Stub\StubOrdered;
use Symbiote\GridFieldExtensions\Tests\Stub\StubOrderedVersioned;
use Symbiote\GridFieldExtensions\Tests\Stub\StubParent;
use Symbiote\GridFieldExtensions\Tests\Stub\StubSubclass;
use Symbiote\GridFieldExtensions\Tests\Stub\StubSubclassOrderedVersioned;
use Symbiote\GridFieldExtensions\Tests\Stub\StubUnorderable;
use Symbiote\GridFieldExtensions\Tests\Stub\ThroughDefiner;
use Symbiote\GridFieldExtensions\Tests\Stub\ThroughIntermediary;
use Symbiote\GridFieldExtensions\Tests\Stub\ThroughBelongs;

/**
 * Tests for the {@link GridFieldOrderableRows} component.
 */
class GridFieldOrderableRowsTest extends SapphireTest
{
    protected static $fixture_file = [
        'GridFieldOrderableRowsTest.yml',
        'OrderableRowsThroughTest.yml',
        // 'OrderablePolymorphicManyToMany.yml'  // TODO: introduce this tests in the next minor release
    ];

    protected static $extra_dataobjects = [
        // PolymorphM2MChild::class,
        // PolymorphM2MMapper::class,
        // PolymorphM2MParent::class,
        StubParent::class,
        StubOrdered::class,
        StubSubclass::class,
        StubUnorderable::class,
        StubOrderableChild::class,
        StubOrderedVersioned::class,
        StubSubclassOrderedVersioned::class,
        ThroughDefiner::class,
        ThroughIntermediary::class,
        ThroughBelongs::class,
    ];

    public function reorderItemsProvider()
    {
        return [
            [StubParent::class . '.parent', 'MyManyMany', 'ManyManySort'],
            [ThroughDefiner::class . '.DefinerOne', 'Belongings', 'Sort'],
            // [PolymorphM2MParent::class . '.ParentOne', 'Children', 'Sort']
        ];
    }

    /**
     * @dataProvider reorderItemsProvider
     */
    public function testReorderItems($fixtureID, $relationName, $sortName)
    {
        $orderable = new GridFieldOrderableRows($sortName);
        $reflection = new ReflectionMethod($orderable, 'executeReorder');
        $reflection->setAccessible(true);

        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        list($parentClass, $parentInstanceID) = explode('.', $fixtureID);
        $parent = $this->objFromFixture($parentClass, $parentInstanceID);

        $grid = new GridField(
            $relationName,
            'Testing Many Many',
            $parent->$relationName()->sort($sortName),
            $config
        );

        $originalOrder = $parent->$relationName()->sort($sortName)->column('ID');
        $desiredOrder = [];

        // Make order non-contiguous, and 1-based
        foreach (array_reverse($originalOrder) as $index => $id) {
            $desiredOrder[$index * 2 + 1] = $id;
        }

        $this->assertNotEquals($originalOrder, $desiredOrder);

        $reflection->invoke($orderable, $grid, $desiredOrder);

        $newOrder = $parent->$relationName()->sort($sortName)->map($sortName, 'ID')->toArray();

        $this->assertEquals($desiredOrder, $newOrder);
    }

    public function testManyManyThroughListSortOrdersAreUsedForInitialRender()
    {
        /** @var ThroughDefiner $record */
        $record = $this->objFromFixture(ThroughDefiner::class, 'DefinerOne');

        $orderable = new GridFieldOrderableRows('Sort');
        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        $grid = new GridField(
            'Belongings',
            'Testing Many Many',
            $record->Belongings()->sort('Sort'),
            $config
        );

        // Get the first record, which would be the first one to have column contents generated
        /** @var ThroughIntermediary $expected */
        $intermediary = $this->objFromFixture(ThroughIntermediary::class, 'One');

        $result = $orderable->getColumnContent($grid, $record, 'irrelevant');

        $this->assertContains(
            'Belongings[GridFieldEditableColumns][' . $record->ID . '][Sort]',
            $result,
            'The field name is indexed under the record\'s ID'
        );
        $this->assertContains(
            'value="' . $intermediary->Sort . '"',
            $result,
            'The value comes from the MMTL intermediary Sort value'
        );
    }

    public function testPolymorphicManyManyListSortOrdersAreUsedForInitialRender()
    {
        $this->markTestSkipped('TODO: Introduce this test in the next minor release (3.3)');

        $record = $this->objFromFixture(PolymorphM2MParent::class, 'ParentOne');

        $orderable = new GridFieldOrderableRows('Sort');
        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        $grid = new GridField(
            'Children',
            'Testing Polymorphic Many Many',
            $record->Children()->sort('Sort'),
            $config
        );

        // Get the first record, which would be the first one to have column contents generated
        $intermediary = $this->objFromFixture(PolymorphM2MMapper::class, 'MapP1ToC1');

        $result = $orderable->getColumnContent($grid, $record, 'irrelevant');

        $this->assertContains(
            'Children[GridFieldEditableColumns][' . $record->ID . '][Sort]',
            $result,
            'The field name is indexed under the record\'s ID'
        );
        $this->assertContains(
            'value="' . $intermediary->Sort . '"',
            $result,
            'The value comes from the MMTL intermediary Sort value'
        );
    }

    public function testSortableChildClass()
    {
        $orderable = new GridFieldOrderableRows('Sort');
        $reflection = new ReflectionMethod($orderable, 'executeReorder');
        $reflection->setAccessible(true);

        $parent = $this->objFromFixture(StubOrdered::class, 'nestedtest');

        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        $grid = new GridField(
            'Children',
            'Children',
            $parent->Children(),
            $config
        );

        $originalOrder = $parent->Children()->column('ID');
        $desiredOrder = array_reverse($originalOrder);

        $this->assertNotEquals($originalOrder, $desiredOrder);

        $reflection->invoke($orderable, $grid, $desiredOrder);

        $newOrder = $parent->Children()->column('ID');

        $this->assertEquals($desiredOrder, $newOrder);
    }

    /**
     * @covers \Symbiote\GridFieldExtensions\GridFieldOrderableRows::getSortTable
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

        $this->assertEquals(
            'StubOrderedVersioned',
            $orderable->setSortField('Sort')->getSortTable($parent->MyHasManySubclassOrderedVersioned())
        );
    }

    public function testReorderItemsSubclassVersioned()
    {
        $orderable = new GridFieldOrderableRows('Sort');
        $reflection = new ReflectionMethod($orderable, 'executeReorder');
        $reflection->setAccessible(true);

        $parent = $this->objFromFixture(StubParent::class, 'parent-subclass-ordered-versioned');

        // make sure all items are published
        foreach ($parent->MyHasManySubclassOrderedVersioned() as $item) {
            $item->publishRecursive();
        }

        // there should be no difference between stages at this point
        $differenceFound = false;
        foreach ($parent->MyHasManySubclassOrderedVersioned() as $item) {
            /** @var  StubSubclassOrderedVersioned|Versioned $item */
            if ($item->stagesDiffer()) {
                $this->fail('Unexpected difference found on stages');
            }
        }

        // reorder items
        $config = new GridFieldConfig_RelationEditor();
        $config->addComponent($orderable);

        $grid = new GridField(
            'TestField',
            'TestField',
            $parent->MyHasManySubclassOrderedVersioned()->sort('Sort', 'ASC'),
            $config
        );

        $originalOrder = $parent->MyHasManySubclassOrderedVersioned()
            ->sort('Sort', 'ASC')
            ->column('ID');

        $desiredOrder = [];

        // Make order non-contiguous, and 1-based
        foreach (array_reverse($originalOrder) as $index => $id) {
            $desiredOrder[$index * 2 + 1] = $id;
        }

        $this->assertNotEquals($originalOrder, $desiredOrder);

        $reflection->invoke($orderable, $grid, $desiredOrder);

        $newOrder = $parent->MyHasManySubclassOrderedVersioned()
            ->sort('Sort', 'ASC')
            ->map('Sort', 'ID')
            ->toArray();

        $this->assertEquals($desiredOrder, $newOrder);

        // reorder should have been handled as versioned - there should be a difference between stages now
        $differenceFound = false;
        foreach ($parent->MyHasManySubclassOrderedVersioned() as $item) {
            if ($item->stagesDiffer()) {
                $differenceFound = true;
                break;
            }
        }

        $this->assertTrue($differenceFound);
    }
}
