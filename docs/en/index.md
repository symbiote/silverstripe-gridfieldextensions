Grid Field Extensions
=====================

Add Existing Search
-------------------

The `GridFieldAddExistingSearchButton` component provides a more complete solution for adding
existing records than a basic autocomplete. It uses the search context constructed by the model
class to provide the search form.

```php
$grid->getConfig()->addComponent(new GridFieldAddExistingSearchButton());
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

**Please NOTE:** There is a limitation when using `GridFieldOrderableRows` on unsaved data objects; namely, that it doesn't work as without data being saved, the list of related objects has no context. Please check `$this->ID` before adding the `GridFieldOrderableRows` component to the grid field config (or even, before adding the gridfield at all).

Configurable Paginator
----------------------

The `GridFieldConfigurablePaginator` component allows you to have a page size dropdown added to your GridField
pagination controls. The page sizes are configurable via the configuration system, or at call time using the public API.
To use this component you should remove the original paginator component first:

```php
$gridField->getConfig()
    ->removeComponentsByType('GridFieldPaginator')
    ->addComponent(new GridFieldConfigurablePaginator());
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
$paginator = new GridFieldConfigurablePaginator(100, array(100, 200, 500));

$paginator->setPageSizes(array(200, 500, 1000));
$paginator->setItemsPerPage(500);
```

The first shown record will be maintained across page size changes, and the number of pages and current page will be
recalculated on each request, based on the current first shown record and page size.


Actions Menu
------------

The `GridFieldActionsMenu` serves as an encapsulation type component, which for each item/row will render a pop-out/dropdown menu to the user - represented on in the UI as the 'meatball' type widget (as opposed to the ever popular 'hamburger' collapsing menu widget). The actions menu will render one of three classes of information:

 - **Links to different edit screens**
   If the item in the current row has an edit form (`getCMSFields`) which contains a root level `TabSet`, this division of items houses an entry for each of it's contained `Tab`s in the nature of e.g. `GridFieldEditButton`, etc. A parameter of `false` can be passed in to skip the first tab, in the case that clicking the row should invoke this action (such as the aforementioned edit button does).
 - **Versioned actions**
   Iff the item is versioned (via the `Versioned` extension), then applicable actions will be rendered in this division. These will be _Publish_, _Unpublish_, and _Archive_ (aka delete). It is worth noting that Publish and Unpublish are _not_ mutually exclusive, as a record can have changes ahead of it's published version (i.e. "Modified" as opposed to "Draft").
 - **Arbitrary extra links**
   Links can be added to any extra division of the pop-out menu, when the components constructor is passed an array of Title, Link, and Type tuples. The title and link mapping to render the text and href of an anchor tag, and the type forming an identifying BEM style CSS class in the form of `actions-menu__${Type}-action` for either styling or JS hooks (probably via entwine). When adding arbitrary links please remember that SilverStripe makes use of the `<base>` tag, and external links will require a protocol as well as a path.

**Examples:**

Basic usage:

```php
$config->addComponent(new GridFieldActionsMenu());
```

Skip the first root tab:

```php
$config->addComponent(new GridFieldActionsMenu(true));
```

Add arbitrary extra links:

```php
$meatballs = new GridFieldActionsMenu();
$meatballs->addActionToGroup([
    [
        'Title' => 'Neato',
        'Link' => 'some/action',
        'Type' => 'extra'
    ]
], 'arbitrary');
$config->addComponent($meatballs);
```
