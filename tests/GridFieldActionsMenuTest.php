<?php

namespace Symbiote\GridFieldExtensions\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use Symbiote\GridFieldExtensions\GridFieldActionsMenu;
use Symbiote\GridFieldExtensions\Tests\Stub\ClassWithTabs;
use Symbiote\GridFieldExtensions\Tests\Stub\VersionedClassWithTabs;
use SilverStripe\Versioned\Versioned;

class GridFieldActionsMenuTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        ClassWithTabs::class,
        VersionedClassWithTabs::class,
    ];

    protected static $required_extensions = [
        VersionedClassWithTabs::class => [
            Versioned::class,
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->component = new GridFieldActionsMenu;
    }

    public function testAugmentColumnsAddsActions()
    {
        $columns = [];
        $this->component->augmentColumns(null, $columns);
        $this->assertContains('Actions', $columns);
    }

    public function testComponentRegistersItselfAsHandlingActions()
    {
        $this->assertSame(['Actions'], $this->component->getColumnsHandled(null));
    }

    public function testCanGetAndSetActions()
    {
        $this->component->setActions(['Foo']);
        $this->assertSame(['Foo'], $this->component->getActions());
    }

    public function testGetSetAndConstructShowFirstTab()
    {
        $component = new GridFieldActionsMenu(false);
        $this->assertFalse($component->getShowFirstTab());

        $component->setShowFirstTab(true);
        $this->assertTrue($component->getShowFirstTab());
    }

    public function testRecordWithRootTabsHasTabsInMenu()
    {
        $record = new ClassWithTabs;
        $result = $this->component->getColumnContent($this->getMockGridField(), $record, 'Actions');

        $this->assertContains('Apple', $result);
        $this->assertContains('Orange', $result);
    }

    public function testVersionedRecordHasVersionedActionsInMenu()
    {
        $record = new VersionedClassWithTabs;
        $result = $this->component->getColumnContent($this->getMockGridField(), $record, 'Actions');

        $this->assertContains('Apple', $result);
        $this->assertContains('Orange', $result);

        $this->assertContains('Publish', $result);
        $this->assertContains('Delete', $result);
    }

    public function testNotShowingTheFirstTabDoesNotShowTheFirstTab()
    {
        $component = new GridFieldActionsMenu;
        $record = new ClassWithTabs;
        $result = $component->getColumnContent($this->getMockGridField(), $record, 'Actions');
        $this->assertContains('Main', $result);

        $component->setShowFirstTab(false);
        $result = $component->getColumnContent($this->getMockGridField(), $record, 'Actions');
        $this->assertNotContains('Main', $result);
    }

    /**
     * @return GridField
     */
    protected function getMockGridField()
    {
        $gridField = GridField::create('Test');
        $gridField->setForm(
            Form::create(
                null,
                'Test',
                FieldList::create(),
                FieldList::create()
            )
        );

        return $gridField;
    }

    public function testGetColumnAttributesHasActionsMenuClass()
    {
        $result = $this->component->getColumnAttributes(null, null, null);
        $this->assertContains('actions-menu', $result['class']);
    }

    public function testColumnMetadataContainsMoreActionsWhenColumnIsActions()
    {
        $this->assertEmpty($this->component->getColumnMetadata(null, 'foo'), 'Default return value is empty');

        $result = $this->component->getColumnMetadata(null, 'Actions');
        $this->assertSame('More Actions', $result['title']);
    }
}
