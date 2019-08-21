<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\UserRepository;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;

interface IDeviceFormFactory
{
    /** @return DeviceForm */
    function create();
}


/**
 * Class DeviceForm
 *
 * @package CmsModule\Forms
 */
class DeviceForm extends BaseForm
{

    /** @var User @inject */
    public $user;

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var UserRepository @inject */
    public $userRepository;

    /** @var CampaignEntity[] */
    private $deviceCampaigns = [];

    private $devicesGroups;

    public function create()
    {
//        $this->addGroup('Nové zařízení');

        $disAllowed = $this->user->isAllowed(DeviceForm::class, 'edit') == false;

        $this->addText('name', 'name')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "name_holder")
            ->addRule(Form::FILLED, 'ruleName')
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 128);

        $this->addText('sn', 'sn')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "sn_holder")
            ->addRule(Form::FILLED, 'ruleSn')
            ->addRule(Form::LENGTH, 'ruleLength', 11);

        $this->addText('snRotate', 'snRotate')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "sn-rotate_holder")
            ->addRule(Form::FILLED, 'ruleSnRotate')
            ->addRule(Form::NUMERIC, 'ruleSnRotateNum')
            ->addRule(Form::RANGE, new Phrase('ruleSnRotateRange', 0), [0, 360]);

        $this->addPassword('psw', 'password', null, 64)
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "password_holder")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'ruleMinLength', 3)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 64);

        $this->addRadioList('tag', $this->getTranslator()->translate('tag'), DeviceEntity::getTags())
            ->setTranslator(null)
            ->setAttribute('class', 'tagColor');

        $this->addTextArea('keywords', $this->getTranslator()->translate('keywords'), DeviceEntity::getTags())
            ->setTranslator(null)
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 65535);

        $this->addCheckbox('address', 'address')
            ->setAttribute('class', 'happy primary')
            ->addCondition(Form::EQUAL, true)
            ->toggle('address-container');

        $this->addText('city', 'city')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "city_holder")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 128);

        $this->addText('street', 'street')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "street_holder")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 255);

        $this->addText('zip', 'psc')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "psc_holder")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 255);


        $this->addSelect('deviceGroup', $this->getTranslator()->translate('group'), $this->getDevicesGroups())
            ->setTranslator(null)
            ->setDisabled($disAllowed)
            ->setPrompt($this->getTranslator()->translate('select'))
//            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $this->addSelect('defaultCampaign', $this->getTranslator()->translate('default_campaign'), $this->getDeviceCampaigns())
            ->setTranslator(null)
            ->setDisabled($disAllowed)
            ->setPrompt($this->getTranslator()->translate('select'))
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $this->addSelect('loopCampaign', $this->getTranslator()->translate('loop_campaign'), $this->getDeviceCampaigns())
            ->setTranslator(null)
            ->setDisabled($disAllowed)
            ->setPrompt($this->getTranslator()->translate('select'))
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $this->addCheckbox('active', 'active')
            ->setDisabled($disAllowed)
            ->setAttribute('class', 'js-switch')
            ->setAttribute('data-size', 'small');


        $this->addSubmit('sendSubmit', 'save')
            ->setDisabled($disAllowed)
            ->setAttribute('class', 'btn btn-success box-list__settings__close js-settingsClose');
//            ->setAttribute('data-dismiss', 'modal');
//            ->onClick[] = [$this, 'success'];


        $this->onValidate[] = [$this, 'validateSN'];
//        $this->onSuccess[] = [$this, 'success'];

        $this->getElementPrototype()->setAttribute('data-name', $this->formName)->addAttributes(['data-id' => $this->getId()]);
        $this->addFormClass(['ajax']);

        return $this;
    }


    public function validateSN(DeviceForm $form, $values)
    {
        if ($values->id == null) {
            return !$this->deviceRepository->findOneBy(['sn' => $values->sn]);
        }

        return true;
    }


    public function success(SubmitButton $button)
    {
        /** @var BaseForm $form */
        $form      = $button->getForm();

        /** @var BasePresenter $presenter */
        $presenter = $form->getPresenter();

        /** @var DeviceEntity $entity */
        $entity = $form->getEntity();


        /*
         * pokud má zařízení skupinu, nemůže být pak kampaň selectována na toto zařízení
         */
        // $this->removeGroupedDevices($entity);

    }


    /**
     * @param DeviceEntity $entity
     */
    private function removeGroupedDevices(DeviceEntity $entity)
    {
        $em = $this->getEntityMapper()->getEntityManager();

        if ($entity->getDeviceGroup()) {
            /** @var CampaignEntity[] $selectEntities */
            if ($selectEntities = $em->getRepository(CampaignEntity::getClassName())->findBy(['devices.id' => $entity])) {

                foreach ($selectEntities as $selectEntity) {
                    $selectEntity->removeDevice($entity);
                }
            }
        }
    }


    private function getDevicesGroups()
    {
        return $this->devicesGroups;
    }


    /**
     * @param DeviceGroupEntity[] $devicesGroups
     *
     * @return $this
     */
    public function setDevicesGroups($devicesGroups)
    {
        $_devicesGroups = [];
        foreach ($devicesGroups as $devicesGroup) {
            $_devicesGroups[$devicesGroup->getId()] = $devicesGroup->getName();
        }

        $this->devicesGroups = $_devicesGroups;
        return $this;
    }

    /**
     * @return CampaignEntity[]
     */
    public function getDeviceCampaigns(): array
    {
        return $this->deviceCampaigns;
    }

    /**
     * @param CampaignEntity[] $deviceCampaigns
     *
     * @return $this
     */
    public function setDeviceCampaigns($deviceCampaigns)
    {
        $_deviceCampaigns = [];
        foreach ($deviceCampaigns as $deviceCampaign) {
            $_deviceCampaigns[$deviceCampaign->getId()] = $deviceCampaign->getName();
        }

        $this->deviceCampaigns = $_deviceCampaigns;
        return $this;
    }



}