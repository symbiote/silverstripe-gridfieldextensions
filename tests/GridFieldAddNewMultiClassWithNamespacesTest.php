<?php

namespace Symbiote\GridFieldExtensions\Tests;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClassHandler;
use Symbiote\GridFieldExtensions\Tests\Stub\NamespacedClass;

class GridFieldAddNewMultiClassWithNamespacesTest extends SapphireTest
{

    public function testGetClassesWithNamespaces()
    {
        $grid = new GridField('TestGridField');
        $grid->setModelClass(NamespacedClass::class);

        $component = new GridFieldAddNewMultiClass();

        $this->assertEquals(
            array(
                'Symbiote-GridFieldExtensions-Tests-Stub-NamespacedClass' => 'NamespacedClass'
            ),
            $component->getClasses($grid),
            'Namespaced classes are sanitised'
        );
    }

    public function testHandleAddWithNamespaces()
    {
        $grid = new GridField('TestGridField');
        $grid->getConfig()->addComponent(new GridFieldDetailForm());
        $grid->setModelClass(NamespacedClass::class);
        $grid->setForm(Form::create(Controller::create(), 'test', FieldList::create(), FieldList::create()));

        $request = new HTTPRequest('POST', 'test');
        $request->setRouteParams(array('ClassName' => 'Symbiote-GridFieldExtensions-Tests-Stub-NamespacedClass'));

        $component = new GridFieldAddNewMultiClass();
        $response = $component->handleAdd($grid, $request);

        $record = new \ReflectionProperty(GridFieldAddNewMultiClassHandler::class, 'record');
        $record->setAccessible(true);
        $this->assertInstanceOf(NamespacedClass::class, $record->getValue($response));
    }
}
