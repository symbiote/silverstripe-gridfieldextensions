<?php
/**
 * Tests for the {@link GridFieldOrderableRows} component.
 */
class GridFieldOrderableRowsTest extends SapphireTest {

	protected $usesDatabase = true;

	/**
	 * @covers GridFieldOrderableRows::getSortTable
	 */
	public function testGetSortTable() {
		$orderable = new GridFieldOrderableRows();

		$parent = new GridFieldOrderableRowsTest_Parent();
		$parent->write();

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Ordered',
			$orderable->getSortTable($parent->HasMany())
		);

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Ordered',
			$orderable->getSortTable($parent->HasManySubclass())
		);

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Ordered',
			$orderable->getSortTable($parent->ManyMany())
		);

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Parent_ManyMany',
			$orderable->setSortField('ManyManySort')->getSortTable($parent->ManyMany())
		);
	}

}

/**#@+
 * @ignore
 */

class GridFieldOrderableRowsTest_Parent extends DataObject {

	private static $has_many = array(
		'HasMany' => 'GridFieldOrderableRowsTest_Ordered',
		'HasManySubclass' => 'GridFieldOrderableRowsTest_Subclass'
	);

	private static $many_many = array(
		'ManyMany' => 'GridFieldOrderableRowsTest_Ordered'
	);

	private static $many_many_extraFields = array(
		'ManyMany' => array('ManyManySort' => 'Int')
	);

}

class GridFieldOrderableRowsTest_Ordered extends DataObject {

	private static $db = array(
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Parent' => 'GridFieldOrderableRowsTest_Parent'
	);

}

class GridFieldOrderableRowsTest_Subclass extends GridFieldOrderableRowsTest_Ordered {
}

/**#@-*/
