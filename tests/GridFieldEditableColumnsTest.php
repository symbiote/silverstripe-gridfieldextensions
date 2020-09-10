<?php

namespace Symbiote\GridFieldExtensions\Tests;

use Symbiote\GridFieldExtensions\Tests\Stub\TestController;
use Symbiote\GridFieldExtensions\Tests\Stub\StubUnorderable;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Dev\SapphireTest;

class GridFieldEditableColumnsTest extends SapphireTest
{
    private function getMockGrid()
    {
        $controller = new TestController('Test');
        $form = new Form($controller, 'TestForm', new FieldList(
            $grid = new GridField('TestGridField')
        ), new FieldList());
        $grid->setModelClass(StubUnorderable::class);
        $grid->setList(StubUnorderable::get());
        return $grid;
    }

    private function getMockRecord($id, $title)
    {
        $record = new StubUnorderable();
        $record->ID = $id;
        $record->Title = $title;
        return $record;
    }

    public function testProvidesEditableFieldsInColumns()
    {
        $grid = $this->getMockGrid();
        $component = new GridFieldEditableColumns();
        $record = $this->getMockRecord(100, "foo");

        $this->assertEquals(
            [ 'Title' ],
            $component->getColumnsHandled($grid)
        );

        $record->setCanEdit(true);
        $column = $component->getColumnContent($grid, $record, 'Title');

        $this->assertInstanceOf(DBHTMLText::class, $column);
        $this->assertRegExp(
            '/<input type="text" name="TestGridField\[GridFieldEditableColumns\]\[100\]\[Title\]" value="foo"[^>]*>/',
            $column->getValue()
        );
    }

    public function testProvidesReadonlyColumnsForNoneditableRecords()
    {
        $grid = $this->getMockGrid();
        $component = new GridFieldEditableColumns();
        $record = $this->getMockRecord(100, "testval");

        $record->setCanEdit(false);
        $column = $component->getColumnContent($grid, $record, 'Title');

        $this->assertInstanceOf(DBHTMLText::class, $column);
        $this->assertRegExp(
            '/<span[^>]*>\s*testval\s*<\/span>/',
            $column->getValue()
        );
    }

    public function testProvidesReadonlyColumnsForReadonlyGrids()
    {
        $grid = $this->getMockGrid();
        $component = new GridFieldEditableColumns();
        $record = $this->getMockRecord(100, "testval");

        $record->setCanEdit(true);
        $grid = $grid->performReadonlyTransformation();

        if (!$grid instanceof GridField) {
            $this->markTestSkipped('silverstripe/framework <4.2.2 doesn\'t support readonly GridFields');
        }

        $column = $component->getColumnContent($grid, $record, 'Title');

        $this->assertInstanceOf(DBHTMLText::class, $column);
        $this->assertRegExp(
            '/<span[^>]*>\s*testval\s*<\/span>/',
            $column->getValue()
        );
    }
}
