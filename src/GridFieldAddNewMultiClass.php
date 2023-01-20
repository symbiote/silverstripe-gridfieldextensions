<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\View\ArrayData;
use ReflectionClass;
use Exception;

/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddNewMultiClass extends AbstractGridFieldComponent implements
    GridField_HTMLProvider,
    GridField_URLHandler
{
    const POST_KEY = 'GridFieldAddNewMultiClass';

    private static $allowed_actions = array(
        'handleAdd'
    );

    // Should we add an empty string to the add class dropdown?
    private static $showEmptyString = true;

    private $fragment;

    private $title;

    /**
     * @var array
     */
    private $classes;

    /**
     * @var string
     */
    private $defaultClass;

    /**
     * @var string
     */
    protected $itemRequestClass = 'Symbiote\\GridFieldExtensions\\GridFieldAddNewMultiClassHandler';

    /**
     * @param string $fragment the fragment to render the button in
     */
    public function __construct($fragment = 'before')
    {
        $this->setFragment($fragment);
        $this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));
    }

    /**
     * Gets the fragment name this button is rendered into.
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Sets the fragment name this button is rendered into.
     *
     * @param string $fragment
     * @return GridFieldAddNewMultiClass $this
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        return $this;
    }

    /**
     * Gets the button title text.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the button title text.
     *
     * @param string $title
     * @return GridFieldAddNewMultiClass $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gets the classes that can be created using this button, defaulting to the model class and
     * its subclasses.
     *
     * @param GridField $grid
     * @return array a map of class name to title
     */
    public function getClasses(GridField $grid)
    {
        $result = array();

        if (is_null($this->classes)) {
            $classes = array_values(ClassInfo::subclassesFor($grid->getModelClass()) ?? []);
            sort($classes);
        } else {
            $classes = $this->classes;
        }

        $kill_ancestors = array();
        foreach ($classes as $class => $title) {
            if (!is_string($class)) {
                $class = $title;
            }
            if (!class_exists($class ?? '')) {
                continue;
            }
            $is_abstract = (($reflection = new ReflectionClass($class)) && $reflection->isAbstract());
            if (!$is_abstract && $class === $title) {
                $title = singleton($class)->i18n_singular_name();
            }

            if ($ancestor_to_hide = Config::inst()->get($class, 'hide_ancestor')) {
                $kill_ancestors[$ancestor_to_hide] = true;
            }

            if ($is_abstract || !singleton($class)->canCreate()) {
                continue;
            }

            $result[$class] = $title;
        }

        if ($kill_ancestors) {
            foreach ($kill_ancestors as $class => $bool) {
                unset($result[$class]);
            }
        }

        $sanitised = array();
        foreach ($result as $class => $title) {
            $sanitised[$this->sanitiseClassName($class)] = $title;
        }

        return $sanitised;
    }

    /**
     * Sets the classes that can be created using this button.
     *
     * @param array $classes a set of class names, optionally mapped to titles
     * @param string $default
     * @return GridFieldAddNewMultiClass $this
     */
    public function setClasses(array $classes, $default = null)
    {
        $this->classes = $classes;
        if ($default) {
            $this->defaultClass = $default;
        }
        return $this;
    }

    /**
     * Sets the default class that is selected automatically.
     *
     * @param string $default the class name to use as default
     * @return GridFieldAddNewMultiClass $this
     */
    public function setDefaultClass($default)
    {
        $this->defaultClass = $default;
        return $this;
    }

    /**
     * Handles adding a new instance of a selected class.
     *
     * @param GridField $grid
     * @param HTTPRequest $request
     * @return GridFieldAddNewMultiClassHandler
     * @throws Exception
     * @throws HTTPResponse_Exception
     */
    public function handleAdd($grid, $request)
    {
        $class     = $request->param('ClassName');
        $classes   = $this->getClasses($grid);
        /** @var GridFieldDetailForm $component */
        $component = $grid->getConfig()->getComponentByType(GridFieldDetailForm::class);

        if (!$component) {
            throw new Exception('The add new multi class component requires the detail form component.');
        }

        if (!$class || !array_key_exists($class, $classes ?? [])) {
            throw new HTTPResponse_Exception(400);
        }

        $unsanitisedClass = $this->unsanitiseClassName($class);
        $handler = Injector::inst()->create(
            $this->itemRequestClass,
            $grid,
            $component,
            new $unsanitisedClass(),
            $grid->getForm()->getController(),
            'add-multi-class'
        );
        $handler->setTemplate($component->getTemplate());

        return $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function getHTMLFragments($grid)
    {
        $classes = $this->getClasses($grid);

        if (!count($classes ?? [])) {
            return array();
        }

        GridFieldExtensions::include_requirements();

        $field = DropdownField::create(
            sprintf('%s[%s]', __CLASS__, $grid->getName()),
            '',
            $classes,
            $this->defaultClass
        );
        if (Config::inst()->get(__CLASS__, 'showEmptyString')) {
            $field->setEmptyString(_t('GridFieldExtensions.SELECTTYPETOCREATE', '(Select type to create)'));
        }
        $field->addExtraClass('no-change-track');

        $data = ArrayData::create(array(
            'Title'      => $this->getTitle(),
            'Link'       => Controller::join_links($grid->Link(), 'add-multi-class', '{class}'),
            'ClassField' => $field
        ));

        return array(
            $this->getFragment() => $data->renderWith(__CLASS__)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getURLHandlers($grid)
    {
        return array(
            'add-multi-class/$ClassName!' => 'handleAdd'
        );
    }

    public function setItemRequestClass($class)
    {
        $this->itemRequestClass = $class;
        return $this;
    }

    /**
     * Sanitise a model class' name for inclusion in a link
     *
     * @param string $class
     * @return string
     */
    protected function sanitiseClassName($class)
    {
        return str_replace('\\', '-', $class ?? '');
    }

    /**
     * Unsanitise a model class' name from a URL param
     *
     * @param string $class
     * @return string
     */
    protected function unsanitiseClassName($class)
    {
        return str_replace('-', '\\', $class ?? '');
    }
}
