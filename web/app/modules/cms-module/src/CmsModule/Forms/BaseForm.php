<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    AbstractForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use Devrun\Doctrine\DoctrineForms\EntityFormTrait;
use Devrun\InvalidArgumentException;
use Flame\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\IControl;
use Nette\Forms\Rendering\DefaultFormRenderer;

interface IBaseForm
{
    /** @return BaseForm */
    function create();
}


class BaseForm extends Form implements IBaseForm
{

    const IN_ARRAY = 'CmsModule\Forms\AdminForm::validateInArray';

    protected $formName;

    protected $formClass = ['form-horizontal'];

    protected $autoButtonClass = true;

    protected $labelControlClass = 'div class="col-sm-3 control-label"';

    protected $controlClass = 'div class=col-sm-9';

    use EntityFormTrait;


    public function create()
    {
        if (null === $this->entityMapper) throw new InvalidArgumentException("set 'inject(true)' for this form (DI settings...), we need auto entity mapper.");
    }


    /**
     * @return $this
     */
    public function bootstrap3Render()
    {
        /** @var $renderer DefaultFormRenderer */
        $renderer                                        = $this->getRenderer();
        $renderer->wrappers['error']['container']        = 'div role=alert class="alert alert-danger m-b-10"';
        $renderer->wrappers['error']['item']             = 'p';
        $renderer->wrappers['controls']['container']     = NULL;
        $renderer->wrappers['pair']['container']         = 'div class=form-group';
        $renderer->wrappers['pair']['.error']            = 'has-error';
        $renderer->wrappers['control']['container']      = $this->controlClass;
        $renderer->wrappers['label']['container']        = $this->labelControlClass;
        $renderer->wrappers['control']['description']    = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

        // make form and controls compatible with Twitter Bootstrap
        $this->getElementPrototype()->addAttributes(['class' => implode(' ', $this->formClass)]);

        foreach ($this->getControls() as $control) {
            if ($this->autoButtonClass && $control instanceof Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;

            } /** @var $control BaseControl */
            elseif ($control instanceof TextBase ||
                $control instanceof SelectBox ||
                $control instanceof MultiSelectBox
            ) {
                $control->getControlPrototype()->addClass('form-control');

            } elseif ($control instanceof Checkbox ||
                $control instanceof CheckboxList ||
                $control instanceof RadioList
            ) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }

        return $this;
    }


    public function addFormClass(array $class)
    {
        $this->formClass = array_merge($this->formClass, $class);
    }

    public function setFormClass(array $class)
    {
        $this->formClass = $class;
    }


    /**
     * @param string $name
     *
     * @return $this
     */
    public function setFormName($name)
    {
        $this->formName = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormName()
    {
        return $this->formName;
    }


    /**
     * @param string $labelControlClass
     *
     * @return $this
     */
    public function setLabelControlClass($labelControlClass)
    {
        $this->labelControlClass = $labelControlClass;
        return $this;
    }

    /**
     * @param string $controlClass
     *
     * @return $this
     */
    public function setControlClass($controlClass)
    {
        $this->controlClass = $controlClass;
        return $this;
    }

    /**
     * @param boolean $autoButtonClass
     *
     * @return $this
     */
    public function setAutoButtonClass($autoButtonClass)
    {
        $this->autoButtonClass = $autoButtonClass;
        return $this;
    }

    public static function validateInArray(IControl $control, $val)
    {
        $return = in_array($val, $control->getValue());
        return $return;
    }


}