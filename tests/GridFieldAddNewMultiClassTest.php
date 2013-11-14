<?php
/**
 * Tests for {@link GridFieldAddNewMultiClass}.
 */
class GridFieldAddNewMultiClassTest extends SapphireTest {

	public function testGetClasses() {
		$grid = new GridField('TestGridField');
		$grid->setModelClass('GridFieldAddNewMultiClassTest_A');

		$component = new GridFieldAddNewMultiClass();

		$this->assertEquals(
			array(
				'GridFieldAddNewMultiClassTest_A' => 'A',
				'GridFieldAddNewMultiClassTest_B' => 'B',
				'GridFieldAddNewMultiClassTest_C' => 'C'
			),
			$component->getClasses($grid),
			'Subclasses are populated by default and sorted'
		);

		$component->setClasses(array(
			'GridFieldAddNewMultiClassTest_B' => 'Custom Title',
			'GridFieldAddNewMultiClassTest_A'
		));

		$this->assertEquals(
			array(
				'GridFieldAddNewMultiClassTest_B' => 'Custom Title',
				'GridFieldAddNewMultiClassTest_A' => 'A'
			),
			$component->getClasses($grid),
			'Sorting and custom titles can be specified'
		);
	}

}

/**#@+
 * @ignore
 */

class GridFieldAddNewMultiClassTest_A {
	public function i18n_singular_name() {
		$class = get_class($this);
		return substr($class, strpos($class, '_') + 1);
	}

	public function canCreate() {
		return true;
	}
}

class GridFieldAddNewMultiClassTest_B extends GridFieldAddNewMultiClassTest_A {}
class GridFieldAddNewMultiClassTest_C extends GridFieldAddNewMultiClassTest_A {}

/**#@-*/
