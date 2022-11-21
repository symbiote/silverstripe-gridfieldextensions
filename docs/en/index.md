Grid Field Extensions
=====================

Add Existing Search
-------------------

The `GridFieldAddExistingSearchButton` component provides a more complete solution for adding
existing records than a basic autocomplete. It uses the search context constructed by the model
class to provide the search form.

```php
$grid->getConfig()->addComponent(GridFieldAddExistingSearchButton::create());
```

Inline Editing
--------------

This example replaces the default data columns component with an inline editable one, and the
default add new button with one that adds new records inline.

```php
$grid = GridField::create(
	'ExampleGrid',
	'Example Grid',
	$this->Items(),
	GridFieldConfig::create()
		->addComponent(GridFieldButtonRow::create('before'))
		->addComponent(GridFieldToolbarHeader::create())
		->addComponent(GridFieldTitleHeader::create())
		->addComponent(GridFieldEditableColumns::create())
		->addComponent(GridFieldDeleteAction::create())
		->addComponent(GridFieldAddNewInlineButton::create())
);
```

You can customise the form fields that are used in the grid by calling `setDisplayFields` on the
inline editing component. By default field scaffolding will be used.

```php
$grid->getConfig()->getComponentByType(GridFieldEditableColumns::class)->setDisplayFields(array(
	'FirstField'  => function($record, $column, $grid) {
		return TextField::create($column);
	},
	'SecondField' => array(
		'title' => 'Custom Title',
		'field' => ReadonlyField::class
	),
	'ThirdField' => array(
		'title' => 'Custom Title Two',
        'callback' => function($record, $column, $grid) {
            return TextField::create($column);
        }
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
use SilverStripe\Forms\GridField\GridFieldAddNewButton;

$grid->getConfig()
     ->removeComponentsByType(GridFieldAddNewButton::class)
     ->addComponent(GridFieldAddNewMultiClass::create());
```

Orderable Rows
--------------

The `GridFieldOrderableRows` component allows drag-and-drop reordering of any list type. The field
used to store the sort is set by passing a constructor parameter to the component, or calling
`setSortField`. For `many_many` relationships, the sort field should normally be an extra field on
the relationship.

```php
// Basic usage, defaults to "Sort" for the sort field.
$grid->getConfig()->addComponent(GridFieldOrderableRows::create());

// Specifying the sort field.
$grid->getConfig()->addComponent(GridFieldOrderableRows::create('SortField'));
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

### Versioning
By default `GridFieldOrderableRows` will handle versioning but won't automatically publish any records. The user will need to go into each record and publish them manually which could get cumbersome for large lists.

You can configure the list to automatically publish a record if the record is the latest version and is already published. This won't publish any records which have draft changes.

```php
$orderable = GridFieldOrderableRows::create()->setRepublishLiveRecords(true);
```

There are caveats with both approaches so consideration should be made for which approach best suits the requirements.

**Please NOTE:** There is a limitation when using `GridFieldOrderableRows` on unsaved data objects; namely, that it doesn't work as without data being saved, the list of related objects has no context. Please check `$this->ID` before adding the `GridFieldOrderableRows` component to the grid field config (or even, before adding the gridfield at all). 

Configurable Paginator
----------------------

The `GridFieldConfigurablePaginator` component allows you to have a page size dropdown added to your GridField
pagination controls. The page sizes are configurable via the configuration system, or at call time using the public API.
To use this component you should remove the original paginator component first:

```php
$gridField->getConfig()
    ->removeComponentsByType('GridFieldPaginator')
    ->addComponent(GridFieldConfigurablePaginator::create());
```

You can configure the page sizes with the configuration system. Note that merging is the default strategy, so to replace
the default sizes with your own you will need to unset the original first, for example:

```php
# File: mysite/_config.php
Config::inst()->remove('GridFieldConfigurablePaginator', 'default_page_sizes');
Config::inst()->update('GridFieldConfigurablePaginator', 'default_page_sizes', array(100, 200, 500));
```

You can also override these at call time:

```php
$paginator = GridFieldConfigurablePaginator::create(100, array(100, 200, 500));

$paginator->setPageSizes(array(200, 500, 1000));
$paginator->setItemsPerPage(500);
```

The first shown record will be maintained across page size changes, and the number of pages and current page will be
recalculated on each request, based on the current first shown record and page size.
