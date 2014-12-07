<?php
/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddNewMultiClass implements GridField_HTMLProvider, GridField_URLHandler {

	private static $allowed_actions = array(
		'handleAdd'
	);

	private $fragment;

	private $title;

	private $classes;

	/**
	 * @var String
	 */
	protected $itemRequestClass = 'GridFieldAddNewMultiClassHandler';

	/**
	 * @param string $fragment the fragment to render the button in
	 */
	public function __construct($fragment = 'before') {
		$this->setFragment($fragment);
		$this->setTitle(_t('GridFieldExtensions.ADD', 'Add'));
	}

	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * Gets the button title text.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the button title text.
	 *
	 * @param string $title
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setTitle($title) {
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
	public function getClasses(GridField $grid) {
		$result = array();

		if(is_null($this->classes)) {
			$classes = array_values(ClassInfo::subclassesFor($grid->getModelClass()));
			sort($classes);
		} else {
			$classes = $this->classes;
		}

		foreach($classes as $class => $title) {
			if(!is_string($class)) {
				$class = $title;
				$title = singleton($class)->i18n_singular_name();
			}

			if(!singleton($class)->canCreate()) {
				continue;
			}

			$result[$class] = $title;
		}

		return $result;
	}

	/**
	 * Sets the classes that can be created using this button.
	 *
	 * @param array $classes a set of class names, optionally mapped to titles
	 * @return GridFieldAddNewMultiClass $this
	 */
	public function setClasses(array $classes) {
		$this->classes = $classes;
		return $this;
	}

	/**
	 * Handles adding a new instance of a selected class.
	 *
	 * @param GridField $grid
	 * @param SS_HTTPRequest $request
	 * @return GridFieldAddNewMultiClassHandler
	 */
	public function handleAdd($grid, $request) {
		$class     = $request->param('ClassName');
		$classes   = $this->getClasses($grid);
		$component = $grid->getConfig()->getComponentByType('GridFieldDetailForm');

		if(!$component) {
			throw new Exception('The add new multi class component requires the detail form component.');
		}

		if(!$class || !array_key_exists($class, $classes)) {
			throw new SS_HTTPResponse_Exception(400);
		}

		$handler = Object::create($this->itemRequestClass,
			$grid, $component, new $class(), $grid->getForm()->getController(), 'add-multi-class'
		);
		$handler->setTemplate($component->getTemplate());

		return $handler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHTMLFragments($grid) {
		$classes = $this->getClasses($grid);

		if(!count($classes)) {
			return array();
		}

		GridFieldExtensions::include_requirements();

		$field = new DropdownField(sprintf('%s[ClassName]', __CLASS__), '', $classes);
		$field->setEmptyString(_t('GridFieldExtensions.SELECTTYPETOCREATE', '(Select type to create)'));
		$field->addExtraClass('no-change-track');

		$data = new ArrayData(array(
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
	public function getURLHandlers($grid) {
		return array(
			'add-multi-class/$ClassName!' => 'handleAdd'
		);
	}

	public function setItemRequestClass($class) {
	  $this->itemRequestClass = $class;
	  return $this;
	}
}
