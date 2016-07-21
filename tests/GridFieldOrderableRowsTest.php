<?php
/**
 * Tests for the {@link GridFieldOrderableRows} component.
 */
class GridFieldOrderableRowsTest extends SapphireTest {

	protected $usesDatabase = true;

	protected $extraDataObjects = array(
		'GridFieldOrderableRowsTest_Parent',
		'GridFieldOrderableRowsTest_Ordered',
		'GridFieldOrderableRowsTest_Subclass',
	);

	/**
	 * @covers GridFieldOrderableRows::getSortTable
	 */
	public function testGetSortTable() {
		$orderable = new GridFieldOrderableRows();

		$parent = new GridFieldOrderableRowsTest_Parent();
		$parent->write();

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Ordered',
			$orderable->getSortTable($parent->MyHasMany())
		);

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Ordered',
			$orderable->getSortTable($parent->MyHasManySubclass())
		);

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Ordered',
			$orderable->getSortTable($parent->MyManyMany())
		);

		$this->assertEquals(
			'GridFieldOrderableRowsTest_Parent_MyManyMany',
			$orderable->setSortField('ManyManySort')->getSortTable($parent->MyManyMany())
		);
	}

}

/**#@+
 * @ignore
 */

class GridFieldOrderableRowsTest_Parent extends DataObject implements TestOnly {

	private static $has_many = array(
		'MyHasMany' => 'GridFieldOrderableRowsTest_Ordered',
		'MyHasManySubclass' => 'GridFieldOrderableRowsTest_Subclass'
	);

	private static $many_many = array(
		'MyManyMany' => 'GridFieldOrderableRowsTest_Ordered'
	);

	private static $many_many_extraFields = array(
		'MyManyMany' => array('ManyManySort' => 'Int')
	);

}

class GridFieldOrderableRowsTest_Ordered extends DataObject implements TestOnly {

	private static $db = array(
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'Parent' => 'GridFieldOrderableRowsTest_Parent'
	);

	private static $belongs_many_many =array(
		'MyManyMany' => 'GridFieldOrderableRowsTest_Parent',
	);

}

class GridFieldOrderableRowsTest_Subclass extends GridFieldOrderableRowsTest_Ordered implements TestOnly {
}

/**#@-*/
