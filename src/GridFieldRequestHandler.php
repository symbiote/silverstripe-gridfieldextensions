<?php

namespace Symbiote\GridFieldExtensions;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldComponent;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\ArrayList;

/**
 * A base utility class for request handlers which present a grid field detail
 * view.
 *
 * This class provides some useful defaults for grid field detail views, such
 * as tabs, breadcrumbs and a back link. Much of this code is extracted from the
 * detail form.
 */
abstract class GridFieldRequestHandler extends RequestHandler
{

    private static $allowed_actions = array(
        'Form'
    );

    /**
     * @var GridField
     */
    protected $grid;

    /**
     * @var GridFieldComponent
     */
    protected $component;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $template = __CLASS__;

    public function __construct(GridField $grid, GridFieldComponent $component, $name)
    {
        $this->grid = $grid;
        $this->component = $component;
        $this->name = $name;

        parent::__construct();
    }

    public function index($request)
    {
        $result = $this->renderWith($this->template);

        if ($request->isAjax()) {
            return $result;
        } else {
            return $this->getTopLevelController()->customise(array(
                'Content' => $result
            ));
        }
    }

    public function Link($action = null)
    {
        return Controller::join_links($this->grid->Link(), $this->name, $action);
    }

    /**
     * This method should be overloaded to build out the detail form.
     *
     * @return Form
     */
    public function Form()
    {
        $form = Form::create(
            $this,
            'SilverStripe\\Forms\\Form',
            FieldList::create($root = TabSet::create('Root', Tab::create('Main'))),
            FieldList::create()
        );

        if ($this->getTopLevelController() instanceof LeftAndMain) {
            $form->setTemplate('LeftAndMain_EditForm');
            $form->addExtraClass('cms-content cms-edit-form cms-tabset center');
            $form->setAttribute('data-pjax-fragment', 'CurrentForm Content');

            $root->setTemplate('CMSTabSet');
            $form->Backlink = $this->getBackLink();
        }

        return $form;
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->grid->getForm()->getController();
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return ArrayList
     */
    public function getBreadcrumbs()
    {
        $controller = $this->getController();

        if ($controller->hasMethod('Breadcrumbs')) {
            return $controller->Breadcrumbs();
        } else {
            return ArrayList::create();
        }
    }

    /**
     * @return string
     */
    protected function getBackLink()
    {
        $controller = $this->getTopLevelController();

        if ($controller->hasMethod('Backlink')) {
            return $controller->Backlink();
        } else {
            return $controller->Link();
        }
    }

    /**
     * @return Controller
     */
    protected function getTopLevelController()
    {
        $controller = $this->getController();

        while ($controller) {
            if ($controller instanceof GridFieldRequestHandler) {
                $controller = $controller->getController();
            } elseif ($controller instanceof GridFieldDetailForm_ItemRequest) {
                $controller = $controller->getController();
            } else {
                break;
            }
        }

        return $controller;
    }
}
