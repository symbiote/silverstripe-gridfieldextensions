---
title: Nested GridFields
summary: Configuring nested GridFields for hierarchical DataObjects, has_many relationships, and ArrayList data
icon: sitemap
---

# Nested gridfields

The [`GridFieldNestedForm`](api:Symbiote\GridFieldExtensions\GridFieldNestedForm) component allows you to nest GridFields in the UI. It can be used with [`DataObject`](api:SilverStripe\ORM\DataObject) subclasses
with the [`Hierarchy`](api:SilverStripe\ORM\Hierarchy\Hierarchy) extension, or by specifying the relation used for nesting.

Here is a small example of basic use of Nested GridField.

```php
namespace App\Admin;

use MyObject;
use SilverStripe\Admin\ModelAdmin;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class MyAdmin extends ModelAdmin
{
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridField = $form->Fields()->dataFieldByName(MyObject::class);
        // Add Nested GridField to main GridField
        $gridField->getConfig()->addComponent(GridFieldNestedForm::create());

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
    private static $has_many = [
        'ChildNodes' => BranchNode::class,
    ];
}
```

You can define your own custom GridField config for the nested GridField configuration by implementing a `getNestedConfig()` method on your nested model. Notice this method should return a [`GridFieldConfig`](api:SilverStripe\Forms\GridField\GridFieldConfig) object.

```php
namespace App\Models;

use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

class ChildNode extends DataObject
{
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
        ParentNode::class,
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
    public function getMyArrayList()
    {
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
    private static array $managed_models = [
        MyDataSet::class,
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

## Additional features and settings

### Maximum nesting level

You can set the maximum number of nested levels using a `$default_max_nesting_level` configuration property. The default value is 10 levels.

```yml
Symbiote\GridFieldExtensions\GridFieldNestedForm:
    default_max_nesting_level: 5
```

You can also set this limit for a specific nested GridField using the `setMaxNestingLevel()` method.

```php
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Symbiote\GridFieldExtensions\GridFieldNestedForm;

public function getNestedConfig(): GridFieldConfig
{
    $config = new GridFieldConfig_RecordEditor();
    $config->addComponent(GridFieldNestedForm::create()->setMaxNestingLevel(5));

    return $config;
}
```

### Updating nested config

You can also modify the default config (a [`GridFieldConfig_RecordEditor`](api:SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor)) via an extension to the nested model class, by implementing `updateNestedConfig`, which will get the config object as the parameter.

```php
namespace App\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldPaginator;

class NestedObjectExtension extends Extension
{
    public function updateNestedConfig(GridFieldConfig &$config)
    {
        $config->removeComponentsByType(GridFieldPaginator::class);
    }
}
```
