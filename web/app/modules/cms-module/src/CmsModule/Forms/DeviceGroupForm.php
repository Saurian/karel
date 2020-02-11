<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceGroupForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\DeviceGroupEntity;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Security\User;

interface IDeviceGroupFormFactory
{
    /** @return DeviceGroupForm */
    function create();
}

class DeviceGroupForm extends BaseForm
{

    /** @var User @inject */
    public $user;


    public function create()
    {

        $disAllowed = $this->user->isAllowed(DeviceForm::class, 'edit') == false;

        $this->addText('name', 'name')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "name_holder")
            ->addRule(Form::FILLED, 'ruleName')
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 255);

        $this->addRadioList('tag', $this->getTranslator()->translate('tag'), DeviceGroupEntity::getTags())
            ->setDisabled($disAllowed)
            ->setTranslator(null)
            ->setAttribute('class', 'tagColor');

        $this->addTextArea('keywords', $this->getTranslator()->translate('keywords'), DeviceGroupEntity::getTags())
            ->setDisabled($disAllowed)
            ->setTranslator(null)
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 65535);


        $this->addCheckbox('active', 'active')
            ->setDisabled($disAllowed)
            ->setAttribute('class', 'js-switch')
            ->setAttribute('data-size', 'small');

        $this->addSubmit('sendSubmit', 'save')
            ->setDisabled($disAllowed)
            ->setAttribute('data-dismiss', 'modal')
            ->setAttribute('class', 'btn btn-success'); // box-list__settings__close

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