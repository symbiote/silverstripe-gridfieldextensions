Grid Field Extensions
=====================

Add Existing Search
-------------------

The `GridFieldAddExistingSearchButton` component provides a more complete solution for adding
existing records than a basic autocomplete. It uses the search context constructed by the model
class to provide the search form.

```php
$grid->getConfig()->addComponent(new GridFieldAddExistingSearchButton()));
```

Inline Editing
--------------

This example replaces the default data columns component with an inline editable one, and the
default add new button with one that adds new records inline.

```php
$grid = new GridField(
	'ExampleGrid',
	'Example Grid',
	$this->Items(),
	GridFieldConfig::create()
		->addComponent(new GridFieldButtonRow('before'))
		->addComponent(new GridFieldToolbarHeader())
		->addComponent(new GridFieldTitleHeader())
		->addComponent(new GridFieldEditableColumns())
		->addComponent(new GridFieldDeleteAction())
		->addComponent(new GridFieldAddNewInlineButton())
);
```

You can customise the form fields that are used in the grid by calling `setDisplayFields` on the
inline editing component. By default field scaffolding will be used.

```php
$grid->getConfig()->getComponentByType('GridFieldEditableColumns')->setDisplayFields(array(
	'FirstField'  => function($record, $column, $grid) {
		return new TextField($column);
	},
	'SecondField' => array(
		'title' => 'Custom Title',
		'field' => 'ReadonlyField'
	)
));
```

Editing data contained in `many_many_extraFields` is supported - just treat it as you would any
other field.

Multi Class Adding
------------------

The `GridFieldAddNewMultiClass` allows the user to select the record type to create when creating
a new record. By default it allows them to select the model class for the grid field, or any
subclasses. You can control the createable classes using the `setClasses` method.

```php
$grid->getConfig()
     ->removeComponentsByType('GridFieldAddNewButton')
     ->addComponent(new GridFieldAddNewMultiClass());
```

Orderable Rows
--------------

The `GridFieldOrderableRows` component allows drag-and-drop reordering of any list type. The field
used to store the sort is set by passing a constructor parameter to the component, or calling
`setSortField`. For `many_many` relationships, the sort field should normally be an extra field on
the relationship.

```php
// Basic usage, defaults to "Sort" for the sort field.
$grid->getConfig()->addComponent(new GridFieldOrderableRows());

// Specifying the sort field.
$grid->getConfig()->addComponent(new GridFieldOrderableRows('SortField'));
```

By default, when you create a new item, it is created with a sort order of "0" - that is, it is added
to the start of the list. The sort order is only set for the first time when the user reorders the items.
If you wish to append newly created items to the end of the list, use an `onBeforeWrite` hook like:

```php
class Item extends DataObject {
	private static $db = array('Sort' => 'Int');
	
	protected function onBeforeWrite() {
		if (!$this->Sort) {
			$this->Sort = Item::get()->max('Sort') + 1;
		}
		
		parent::onBeforeWrite();
	}
}
```
