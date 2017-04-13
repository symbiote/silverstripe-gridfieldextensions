<?php

namespace SilverStripeAustralia\Test;

use SapphireTest;
use SS_HTTPRequest;
use Form, Controller, FieldList;
use GridField, GridFieldDetailForm, GridFieldAddNewMultiClass;

class GridFieldAddNewMultiClassWithNamespacesTest extends SapphireTest {

	public function testGetClassesWithNamespaces() {
		$grid = new GridField('TestGridField');
		$grid->setModelClass('SilverStripeAustralia\\Test\\NamespacedClass');

		$component = new GridFieldAddNewMultiClass();

		$this->assertEquals(
			array(
				'SilverStripeAustralia-Test-NamespacedClass' => 'NamespacedClass'
			),
			$component->getClasses($grid),
			'Namespaced classes are sanitised'
		);
	}

	public function testHandleAddWithNamespaces() {
		$grid = new GridField('TestGridField');
		$grid->getConfig()->addComponent(new GridFieldDetailForm());
		$grid->setModelClass('SilverStripeAustralia\\Test\\NamespacedClass');
		$grid->setForm(Form::create('test', Controller::create(), FieldList::create(), FieldList::create()));

		$request = new SS_HTTPRequest('POST', 'test');
		$request->setRouteParams(array('ClassName' => 'SilverStripeAustralia-Test-NamespacedClass'));

		$component = new GridFieldAddNewMultiClass();
		$response = $component->handleAdd($grid, $request);

		$record = new \ReflectionProperty('GridFieldAddNewMultiClassHandler', 'record');
		$record->setAccessible(true);
		$this->assertInstanceOf('SilverStripeAustralia\\Test\\NamespacedClass', $record->getValue($response));
	}

}

/**#@+
 * @ignore
 */

class NamespacedClass {
	public function i18n_singular_name() {
		return 'NamespacedClass';
	}

	public function canCreate() {
		return true;
	}
}

/**#@-*/
