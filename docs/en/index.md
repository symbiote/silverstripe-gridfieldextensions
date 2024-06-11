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

Nested GridFields
-----------------

The `GridFieldNestedForm` component allows you to nest GridFields in the UI. It can be used with `DataObject` subclasses
with the `Hierarchy` extension, or by specifying the relation used for nesting.

Here is a small example of basic use of Nested GridField.

```php
namespace App\Admin;

use MyObject;
use SilverStripe\Admin\ModelAdmin;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class MyAdmin extends ModelAdmin
{
    //...

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $grid = $form->Fields()->dataFieldByName(MyObject::class);
        // Add Nested GridField to main GridField
        $grid->getConfig()->addComponent(GridFieldNestedForm::create());

        return $form;
    }
}
```

There are several ways to use Nested GridField. The implementation depends on the type of data you will be using in your GridField.
For instance, if there is a `DataObject` that has the `Hierarchy` extension, you can use the following approach.

As an example, here we can use a typical hierarchy of the `Group` model, where another `Group` can serve as a parent.

```php
namespace App\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Group;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class MySecurityAdminExtension extends Extension
{
    public function updateGridFieldConfig($config)
    {
        if ($this->owner->getModelClass() === Group::class) {
            $config->addComponent(GridFieldNestedForm::create());
        }
    }
}
```

Or also view a list of all members of a given group. Notice we call `setRelationName()` to explicitly state the relation which should be displayed in the Nested GridField.

```php
namespace App\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Group;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class MySecurityAdminExtension extends Extension
{
    public function updateGridFieldConfig($config)
    {
        if ($this->owner->getModelClass() === Group::class) {
            $config->addComponent(GridFieldNestedForm::create()->setRelationName('Members'));
        }
    }
}
```

```yml
SilverStripe\Admin\SecurityAdmin:
extensions:
    - App\Extensions\MySecurityAdminExtension
```

Another way to use Nested GridField together with `DataObjects` that do not have the `Hierarchy` extension but have `has_many` relationships with other objects.
Let's say you have the following `DataObject` that has multiple levels of relationships, and an admin section where the data of this object will be displayed.
You want the user to be able to view information regarding this object and all nested objects on the same page, without the need to navigate to each object individually.
In this case, you can use the following approach.

```php
namespace App\Models;

use SilverStripe\ORM\DataObject;

class ParentNode extends DataObject
{
    //...

    private static $has_many = [
        'ChildNodes' => BranchNode::class,
    ];
}
```

You can define your own custom GridField config for the nested GridField configuration by implementing a `getNestedConfig()` method on your nested model. Notice this method should return a `GridFieldConfig` object.

```php
namespace App\Models;

use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class ChildNode extends DataObject
{
    //...

    private static $has_one = [
        'ParentNode' => ParentNode::class,
    ];

    private static $has_many = [
        'GrandChildNodes' => GrandChildNode::class,
    ];

    public function getNestedConfig(): GridFieldConfig
    {
        $config = new GridFieldConfig_RecordEditor();
        $config->addComponent(GridFieldNestedForm::create()->setRelationName('GrandChildNodes'));

        return $config;
    }
}
```

```php
namespace App\Models;

use SilverStripe\ORM\DataObject;

class GrandChildNode extends DataObject
{
    //...

    private static $has_one = [
        'ChildNode' => ChildNode::class,
    ];
}
```

```php
namespace App\Admin;

use App\Models\ParentNode;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;


class MyAdminSection extends ModelAdmin
{
    private static string $url_segment = 'my-admin-section';
    private static string $menu_title = 'My Admin Section';

    private static array $managed_models = [
        ParentNode::class
    ];

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();
        $config->addComponent(GridFieldNestedForm::create()->setRelationName('ChildNodes'));

        return $config;
    }
}
```

There is also the possibility to use Nested GridField with the data structure ArrayList. To do this, you can use the following approach.

```php
namespace App\Models;

use MyDataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

class MyDataSet extends DataObject
{
    //...

    public function getMyArrayList() {
        $list = ArrayList::create();
        $data = MyDataObject::get();

        foreach ($data as $value) {
            $list->push($value);
        }

        return $list;
    }
}
```

```php
namespace App\Admin;

use App\Models\MyDataSet;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class MyAdminSection extends ModelAdmin
{
    //...

    private static array $managed_models = [
        MyDataSet::class
    ];

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();
        $config->addComponent(GridFieldNestedForm::create()->setRelationName('getMyArrayList'));

        return $config;
    }
}
```

Notice that instead of the name of a relation, we're passing a method name into `getMyArrayList()`. That method must return an instance of `SS_List` to be used in the GridField.


#### Additional features and settings

1. You can set the maximum number of nested levels using a `$default_max_nesting_level` configuration property. The default value is 10 levels.

    ```yml
    Symbiote\GridFieldExtensions\GridFieldNestedForm:
        default_max_nesting_level: 5
    ```

    You can also set this limit for a specific nested GridField using the `setMaxNestingLevel()` method.

    ```php
    public function getNestedConfig(): GridFieldConfig
    {
        $config = new GridFieldConfig_RecordEditor();
        $config->addComponent(GridFieldNestedForm::create()->setMaxNestingLevel(5));

        return $config;
    }
    ```

1. You can also modify the default config (a `GridFieldConfig_RecordEditor`) via an extension to the nested model class, by implementing
`updateNestedConfig`, which will get the config object as the parameter.

    ```php
    namespace App\Extensions;

    use SilverStripe\Core\Extension;

    class NestedObjectExtension extends Extension
    {
        public function updateNestedConfig(GridFieldConfig &$config)
        {
            $config->removeComponentsByType(GridFieldPaginator::class);
        }
    }
    ```
