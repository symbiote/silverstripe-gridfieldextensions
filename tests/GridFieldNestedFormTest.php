<?php

namespace Symbiote\GridFieldExtensions\Tests;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\ArrayList;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;
use Symbiote\GridFieldExtensions\Tests\Stub\StubHierarchy;
use Symbiote\GridFieldExtensions\Tests\Stub\StubOrdered;
use Symbiote\GridFieldExtensions\Tests\Stub\StubParent;
use Symbiote\GridFieldExtensions\Tests\Stub\TestController;

class GridFieldNestedFormTest extends SapphireTest
{
    protected static $fixture_file = 'GridFieldNestedFormTest.yml';

    protected static $extra_dataobjects = [
        StubHierarchy::class,
        StubParent::class,
        StubOrdered::class
    ];

    public function testHierarchy()
    {
        // test that GridFieldNestedForm works with hierarchy objects
        $parent = $this->objFromFixture(StubHierarchy::class, 'item1');
        $list = new ArrayList([$parent]);
        $config = new GridFieldConfig_RecordEditor();
        $config->addComponent($nestedForm = new GridFieldNestedForm());

        $controller = new TestController('Test');
        $form = new Form($controller, 'TestForm', new FieldList(
            $gridField = new GridField(__FUNCTION__, 'test', $list, $config)
        ), new FieldList());

        $request = new HTTPRequest('GET', '/');
        $itemEditForm = $nestedForm->handleNestedItem($gridField, $request, $parent);
        $this->assertNotNull($itemEditForm);
        $nestedGridField = $itemEditForm->Fields()->first();
        $this->assertNotNull($nestedGridField);
        $list = $nestedGridField->getList();
        $this->assertEquals(1, $list->count());

        $child1 = $this->objFromFixture(StubHierarchy::class, 'item1_1');
        $this->assertEquals($child1->ID, $list->first()->ID);
        $nestedForm = $nestedGridField->getConfig()->getComponentByType(GridFieldNestedForm::class);
        $itemEditForm = $nestedForm->handleNestedItem($gridField, $request, $child1);
        $this->assertNotNull($itemEditForm);

        $nestedGridField = $itemEditForm->Fields()->first();
        $this->assertNotNull($nestedGridField);
        $list = $nestedGridField->getList();
        $this->assertEquals(1, $list->count());
        $child2 = $this->objFromFixture(StubHierarchy::class, 'item1_1_1');
        $this->assertEquals($child2->ID, $list->first()->ID);
    }

    public function testHasManyRelation()
    {
        // test that GridFieldNestedForm works with HasMany relations
        $parent = $this->objFromFixture(StubParent::class, 'parent1');
        $list = new ArrayList([$parent]);
        $config = new GridFieldConfig_RecordEditor();
        $config->addComponent($nestedForm = new GridFieldNestedForm());
        $nestedForm->setRelationName('MyHasMany');

        $controller = new TestController('Test');
        $form = new Form($controller, 'TestForm', new FieldList(
            $gridField = new GridField(__FUNCTION__, 'test', $list, $config)
        ), new FieldList());

        $request = new HTTPRequest('GET', '/');
        $itemEditForm = $nestedForm->handleNestedItem($gridField, $request, $parent);
        $this->assertNotNull($itemEditForm);
        $nestedGridField = $itemEditForm->Fields()->first();
        $this->assertNotNull($nestedGridField);
        $list = $nestedGridField->getList();
        $this->assertEquals(2, $list->count());

        $child1 = $this->objFromFixture(StubOrdered::class, 'child1');
        $this->assertEquals($child1->ID, $list->first()->ID);
    }
}
