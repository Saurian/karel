<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceGroupForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;

interface IDeviceGroupFormFactory
{
    /** @return DeviceGroupForm */
    function create();
}

class DeviceGroupForm extends BaseForm
{

    public function create()
    {
        $this->addText('name', 'name')
            ->setAttribute('placeholder', "name_holder")
            ->addRule(Form::FILLED, 'ruleName')
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 255);

        $this->addCheckbox('active', 'active')
//            ->setDisabled($disAllowed)
            ->setAttribute('class', 'js-switch')
            ->setAttribute('data-size', 'small');

        $this->addSubmit('sendSubmit', 'save')
            ->setAttribute('data-dismiss', 'modal')
            ->setAttribute('class', 'btn btn-success box-list__settings__close');

//        $this->onSuccess[] = [$this, 'success'];
        $this->addFormClass(['ajax']);

        $this->getElementPrototype()->setAttribute('data-name', $this->formName);
        return $this;
    }


    /**
     * @deprecated
     *
     * @param BaseForm $form
     * @param          $values
     */
    public function success(BaseForm $form, $values)
    {
        $entity = $form->getEntity();
        $em = $form->getEntityMapper()->getEntityManager();
        $em->persist($entity)->flush();
    }






}