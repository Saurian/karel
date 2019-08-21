<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    DeviceGroupPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Controls\IDeviceControlFactory;
use CmsModule\Controls\IDevicesControlFactory;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Facades\CampaignFacade;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\DeviceForm;
use CmsModule\Forms\DeviceGroupForm;
use CmsModule\Forms\IDeviceFormFactory;
use CmsModule\Forms\IDeviceGroupFormFactory;
use CmsModule\Repositories\CampaignRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;

class DeviceGroupPresenter extends BasePresenter
{

    /** @var IDeviceControlFactory @inject */
    public $deviceControlFactory;

    /** @var IDevicesControlFactory @inject */
    public $devicesControlFactory;

    /** @var DeviceFacade @inject */
    public $deviceFacade;

    /** @var CampaignFacade @inject */
    public $campaignFacade;

    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var IDeviceGroupFormFactory @inject */
    public $deviceGroupFormFactory;

    /** @var IDeviceFormFactory @inject */
    public $deviceFormFactory;


    public function handleItemsNested($nestedData, $elementId)
    {
        $nestedData = json_decode($nestedData);

        $sortData = array_map(function ($data) {
            return $data->id;
        }, $nestedData);

        $positionedData = array_flip($sortData);

        $rows      = $this->getRows();
        $entity    = $rows[$elementId];
        $indexRows = array_values($rows);
        $targetRow = $indexRows[$positionedData[$elementId]];

        $targetPosition = $targetRow->position;
        $em             = $this->deviceFacade->getEntityManager();

        $entity->position = $targetPosition;
        $em->persist($entity)->flush();

        $this->payload->_nested_success = true;
        $this->ajaxRedirect('this', null, ['items', 'flash']);
    }


    public function handleDetail($id)
    {
        $this->template->toggle_detail = $id;
        $this->payload->_toggle_detail = $id;

        $this->deviceFacade->getDeviceGroupRepository()->setOpenDetailDeviceGroup($id);
        $this->ajaxRedirect('this', null, ['items']);
    }


    public function handleSetFilter($active)
    {
        $repository = $this->deviceFacade->getDeviceGroupRepository();
        $repository->setFilterActive($active);

        $filter = $repository->getFilterActive();

        $message = "set";
        if ($filter === "1") {
            $message = "setActives";

        } elseif ($filter === "0") {
            $message = "setNonActives";

        } elseif ($filter === null) {
            $message = "setAll";
        }

        $translator = $this->translateMessage();
        $title      = $translator->translate('deviceGroupPage.management');
        $this->flashMessage($translator->translate("deviceGroupPage.filter.$message"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('this', null, ['devices', 'flash']);
    }


    public function handleToggleActive($did, $checked)
    {
        $repository = $this->deviceFacade->getDeviceGroupRepository();

        /** @var DeviceGroupEntity $element */
        if ($element = $repository->find($did)) {

            $this->deviceFacade->setGroupActive($element, $checked);

            $translator = $this->translateMessage();
            $message    = $element->isActive()
                ? $translator->translate("deviceGroupPage.device_group_active", null, ['name' => $element->getName()])
                : $translator->translate("deviceGroupPage.device_group_non_active", null, ['name' => $element->getName()]);

            $title = $translator->translate('deviceGroupPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        }

        if ($repository->existFilterActive()) {

            if (($filterActive = $repository->getFilterActive()) !== null) {
                $this->payload->_switchery_redraw = true;
                $this->payload->_filter_toggle    = true;
                $this->ajaxRedirect('this', null, ['devices', 'filter', 'flash']);
                return;
            }
        }

        $this->ajaxRedirect('this', null, ['filter', 'flash']);
    }


    public function renderDefault()
    {
        $repository = $this->deviceFacade->getDeviceGroupRepository();
        $query      = $this->getUserAllowedDevicesQuery();

        $rows = $repository->fetch($query);

        $total  = $rows->count();
        $active = $nonActive = 0;

        foreach ($rows as $row) {
            if ($row->active) $active++;
            if (!$row->active) $nonActive++;
        }

        $this->template->deviceGroups = $this->getRows();

        $this->template->allDeviceCount       = $total;
        $this->template->activeDeviceCount    = $active;
        $this->template->nonActiveDeviceCount = $nonActive;
        $this->template->newDeviceGroup       = $this->deviceFacade->isNewDeviceGroup();
    }


    private function getUserAllowedDevicesQuery()
    {
        return $this->deviceFacade->getDeviceGroupRepository()->getUserAllowedQuery($this->user);
    }


    /**
     * @return DeviceEntity[]
     */
    public function getRows()
    {
        static $rows = null;

        if (null === $rows) {
            $query = $this->getUserAllowedDevicesQuery();

            $repository = $this->deviceFacade->getDeviceGroupRepository();

            if ($repository->existFilterActive()) {
                $filterActive = $repository->getFilterActive();

                switch ($filterActive) {
                    case true:
                        $query->isActive();
                        break;

                    case false:
                        $query->isNotActive();
                        break;
                }
            }

            $query->orderByPosition();
            $rows = $this->setRows($repository->fetch($query));
        }

        return $rows;
    }


    /**
     * @param DeviceEntity[] $rows
     *
     * @return array
     */
    public function setRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->getId()] = $row;
        }

        return $_rows;
    }


    /**
     * front forms
     *
     * @param $name
     *
     * @return Multiplier
     */
    protected function createComponentDeviceGroupsForm($name)
    {
        $self = $this;

        return new Multiplier(function ($id) use ($self, $name) {

            $entity = $self->getRows()[$id];
            $form   = new Form();

            $form->addCheckbox('active', $this->translateMessage()->translate('forms.campaignsDetailForm.active'))
                ->setDisabled($this->user->isAllowed(DeviceForm::class, 'edit') == false)
                ->setAttribute('class', 'js-switch')
                ->setAttribute('data-size', 'small');

            $form->getElementPrototype()
                ->addAttributes([
                    'class'     => 'ajax',
                    'data-name' => "CampaignForm",
                    'data-id'   => $id,
                    'data-ajax' => "false",
                ]);

            $form->setDefaults([
                'active' => $entity->isActive(),
            ]);

            return $form;
        });


    }


    /**
     * detail forms
     *
     * @param $name
     *
     * @return Multiplier
     */
    protected function createComponentDeviceGroupsDetailForm($name)
    {
        $self = $this;

        return new Multiplier(function ($index) use ($self, $name) {
            /** @var DeviceGroupEntity $entity */
            $entity = $self->getRows()[$index];

            $form = $this->deviceGroupFormFactory->create();
            $form->setTranslator($this->translator->domain("messages.forms.deviceGroupDetailForm"));
            $form->setFormName($name);
            $form->setId($index);

            $form->create();
            $form->bootstrap3Render();
            $form->bindEntity($entity);
            $form->onSuccess[] = function (DeviceGroupForm $form, $values) {

                $translator = $this->translateMessage();

                try {
                    /** @var DeviceGroupEntity $entity */
                    $entity = $form->getEntity();
                    $this->deviceFacade->getEntityManager()->persist($entity)->flush();

                    $title   = $translator->translate('deviceGroupPage.management');
                    $message = (null == $entity->getId())
                        ? $translator->translate("deviceGroupPage.device_group_added", null, ['name' => $entity->getName()])
                        : $translator->translate("deviceGroupPage.device_group_updated", null, ['name' => $entity->getName()]);

                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

                } catch (UniqueConstraintViolationException $e) {
                    $title   = $translator->translate('deviceGroupPage.management');
                    $message = $translator->translate('deviceGroupPage.device_update_error', $e->getErrorCode(), ['name' => $entity->getName()]);
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
                }


                $this->payload->_switchery_redraw = true;
                $this->payload->_success          = true;
                $this->ajaxRedirect('this', null, ['devices', 'items', 'flash']);

            };

            return $form;
        });
    }


    /**
     * modal add new group
     *
     * @return \CmsModule\Forms\DeviceGroupForm
     */
    protected function createComponentDeviceGroupForm($name)
    {
        $form = $this->deviceGroupFormFactory->create();
        $form->setTranslator($this->translator->domain("messages.forms.deviceGroupDetailForm"));
        $form->setFormName($name);

        if ($this->deviceFacade->isNewDeviceGroup()) {
            $this->deviceFacade->cleanNewDeviceGroup();
        }

        $form->create();
        $form->bootstrap3Render();
        $form->bindEntity($entity = new DeviceGroupEntity(''));

        $form->onSuccess[] = function (BaseForm $form) {

            /** @var DeviceGroupEntity $entity */
            $entity = $form->getEntity();

            /*
             * add new device group to user
             */
            $userEntity = $this->userEntity;
            $userEntity->addDeviceGroup($entity);

            $this->deviceFacade->getEntityManager()->persist($entity)->persist($userEntity)->flush();

            $this->payload->lastDeviceGroupId   = $entity->getId();
            $this->payload->lastDeviceGroupName = $entity->getName();

            $translator = $this->translateMessage();
            $title      = $translator->translate('deviceGroupPage.management');
            $message    = $translator->translate('deviceGroupPage.device_group_added', null, ['name' => $entity->getName()]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

            $this->payload->_switchery_redraw = true;
            $this->payload->_scroll_id        = $entity->getId();
            $form->setValues([], true);
            $this->ajaxRedirect('this', null, ['devices', 'deviceGroupFormModal', 'deviceGroupForm', 'flash']);

        };

        return $form;
    }


}