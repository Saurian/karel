<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Entities\TemplateEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Forms\Controls\BootstrapDateRangePicker;
use CmsModule\InvalidArgumentException;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Devrun\Doctrine\DoctrineForms\ToManyContainer;
use Devrun\Php\PhpInfo;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tracy\Debugger;

interface ICampaignFormFactory
{
    /** @return CampaignForm */
    function create();
}

/**
 * Class CampaignForm
 *
 * @package CmsModule\Forms
 * @method addBootstrapDateRangePicker($name, $label)
 */
class CampaignForm extends BaseForm
{

    /** @var User @inject */
    public $user;


    protected $autoButtonClass = false;

    /** @var UserEntity */
    private $userEntity;

    /** @var CampaignEntity */
    private $campaignEntity;

    /** @var MediumDataEntity[] */
    private $mediumDataEntities = [];

    /** @var DeviceEntity[] */
    private $devices = [];

    /** @var DeviceGroupEntity[] */
    private $devicesGroups = [];

    /** @var TemplateEntity[] */
    private $templates = [];


    public function create()
    {
//        if (!$this->campaignEntity) throw new InvalidArgumentException('setCampaignEntity($campaign) first');

        $disAllowed = $this->user->isAllowed(CampaignForm::class, 'edit') == false;

        $this->addSubmit('sendSubmit', 'save')
            ->setAttribute('class', 'btn btn-success box-list__settings__close js-settingsClose')
            ->setAttribute('data-dismiss', 'modal')
            ->onClick[] = [$this, 'success'];


        $this->addText('name', 'name')
            ->setDisabled($disAllowed)
            ->setAttribute('placeholder', "campaignName")
            ->addRule(Form::FILLED, 'ruleRequired')
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 255);

        /** @var BootstrapDateRangePicker $dateRangePicker */
        $dateRangePicker = $this->addBootstrapDateRangePicker('realizedFrom', 'realized');
        $dateRangePicker->setOption(IComponentMapper::FIELD_RANGE_NAME, 'realizedTo');

        $dateFrom = new DateTime("2019-01-01");
        $dateTo = new DateTime("2025-02-14");

        $dateRangePicker
            ->setDisabled($disAllowed)
            ->setTimePicker(true)
            ->setAttribute('class', "input-daterange-timepicker")
            ->setAttribute('placeholder', "Trvání kampaně")
            ->setClass('form-control')
//            ->setMinDate($dateFrom)
//            ->setMaxDate($dateTo)
            ->addRule(Form::FILLED, 'ruleCampaignRealization')
            ->addRule(BootstrapDateRangePicker::DATETIME_RANGE, new Phrase('ruleCampaignRealizationRange', NULL, ["from"=> $dateFrom->format('j. n. Y'), 'to' => $dateTo->format('j. n. Y') ]), array($dateFrom, $dateTo));


        $this->addCheckbox('active', 'active')
            ->setDisabled($disAllowed)
            ->setAttribute('class', 'js-switch')
            ->setAttribute('data-size', 'small');


        $devices = $this->addCheckboxList('devices', $this->getTranslator()->translate('devicesLabel'), $this->devices)
            ->setTranslator(null)
//            ->setDefaultValue([29])  // any value turn off auto setting
            ->setDisabled($disAllowed)
//            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['devices IN' => [29]]);
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['deviceGroup' => null]);



        $devicesGroups = $this->addCheckboxList('devicesGroups', $this->getTranslator()->translate('groups'), $this->devicesGroups)
            ->setTranslator(null)
//            ->setDefaultValue([29])  // any value turn off auto setting
            ->setDisabled($disAllowed)
//            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name');
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items
//            ->setOption(IComponentMapper::ITEMS_FILTER, ['deviceGroup' => null]);

        $devices
//            ->addConditionOn($this['sendSubmit'], Form::SUBMITTED)
            ->addCondition(Form::BLANK)
            ->addConditionOn($this['devicesGroups'], Form::BLANK)
            ->addRule(Form::FILLED, 'ruleDeviceOrGroup');

        $devicesGroups
//            ->addConditionOn($this['sendSubmit'], Form::SUBMITTED)
            ->addCondition(Form::BLANK)
            ->addConditionOn($this['devices'], Form::BLANK)
            ->addRule(Form::FILLED, 'ruleDeviceOrGroup');



        $this->addRadioList('tag', $this->getTranslator()->translate('tag'), CampaignEntity::getTags())
            ->setTranslator(null)
            ->setAttribute('class', 'tagColor')
            ->setDisabled($disAllowed);
            //->addRule(Form::FILLED, 'ruleTag');

        $this->addTextArea('keywords', $this->getTranslator()->translate('keywords'), CampaignEntity::getTags())
            ->setTranslator(null)
            ->setDisabled($disAllowed)
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 65535);


        /*
         * toggle container for more options
         */
        $condition = $this->addCheckbox('keywordsToggle', 'keywordsToggle')
            ->setAttribute('class', 'check')
            ->addCondition(form::EQUAL, true)
            ->toggle('keyword-container');


//        $this->onSuccess[] = [$this, 'success'];

        $this->addFormClass(['ajax']);
        $this->getElementPrototype()
            ->addAttributes([
//                'data-dismiss' => 'modal',
                'data-name' => $this->formName,
                'data-id' => $this->getId(),
                'data-ajax' => "false",
            ]);

        return $this;
    }


    private function addKeywordControl(\Nette\Forms\Container $medium, $disAllowed, $id)
    {
        $medium->addTextArea('keywords', 'keywords', null, 4)
            ->setDisabled($disAllowed)
            ->setAttribute('class', 'form-control')
//            ->setAttribute('data-id', $id)
            ->setOption('id', $id)
            ->getLabelPrototype()->setAttribute('class', 'control-label control-label-slim text-left')
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 65535);
    }


    /**
     * @deprecated not use
     *
     * @param SubmitButton $button
     */
    public function success(SubmitButton $button)
    {
        /** @var BaseForm $form */
        $form = $button->getForm();
        $values = $form->getValues();

        /** @var CampaignEntity $entity */
        $entity = $form->getEntity();
        $mediaData = $entity->getMediaData();
    }


    public function changeTemplate(SubmitButton $button) {

        /** @var BaseForm $form */
        $form   = $button->getForm();

        /** @var CampaignEntity $entity */
        $entity = $form->getEntity();

        $em = $form->getEntityMapper()->getEntityManager();
        if ($entities = $em->getRepository(MediumDataEntity::getClassName())->findBy(['campaign' => $entity])) {
            $em->remove($entities);
        }


        $em->persist($entity)->flush();
    }


    /**
     * @param CampaignEntity $campaignEntity
     *
     * @return $this
     */
    public function setCampaignEntity(CampaignEntity $campaignEntity)
    {
        $this->campaignEntity = $campaignEntity;

        foreach ($campaignEntity->getMediaData() as $mediumDataEntity) {
            if ($mediumDataEntity->getId())
                $this->mediumDataEntities[$mediumDataEntity->getId()] = $mediumDataEntity;
        }

        return $this;
    }

    /**
     * @param DeviceEntity[] $devices
     *
     * @return $this
     */
    public function setDevices($devices)
    {
        $_devices = [];
        foreach ($devices as $device) {
            $_devices[$device->getId()] = $device->getName();
        }

        $this->devices = $_devices;
        return $this;
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
     * @param UserEntity $userEntity
     *
     * @return $this
     */
    public function setUserEntity(UserEntity $userEntity)
    {
        $this->userEntity = $userEntity;
        return $this;
    }



}