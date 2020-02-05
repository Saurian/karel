<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DriversPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Controls\IDeviceControlFactory;
use CmsModule\Controls\IDevicesControlFactory;
use CmsModule\Controls\IPlayListControlFactory;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Facades\CampaignFacade;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\IDeviceFormFactory;
use CmsModule\Forms\IDeviceGroupFormFactory;
use CmsModule\InvalidArgumentException;
use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\TargetGroupRepository;
use Devrun\CmsModule\Controls\IDeviceTargetGroupsControlFactory;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Html;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;

class DevicePresenter extends BasePresenter
{

    /** @var IDeviceControlFactory @inject */
    public $deviceControlFactory;

    /** @var IDevicesControlFactory @inject */
    public $devicesControlFactory;

    /** @var IPlayListControlFactory @inject */
    public $playListControlFactory;

    /** @var IDeviceTargetGroupsControlFactory @inject */
    public $deviceTargetGroupsControlFactory;

    /** @var IDeviceGroupFormFactory @inject */
    public $deviceGroupFormFactory;

    /** @var IDeviceFormFactory @inject */
    public $deviceFormFactory;


    /** @var DeviceFacade @inject */
    public $deviceFacade;

    /** @var CampaignFacade @inject */
    public $campaignFacade;

    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var TargetGroupRepository @inject */
    public $targetGroupRepository;


    /** @var integer @persistent editace zařízení */
    public $editDevice;

    /** @var integer @persistent editace skupiny zařízení*/
    public $editDeviceGroup;

    /** @var integer @persistent editace cílových skupin na zařízení */
    public $editDeviceTargetGroups;

    /** @var integer @persistent editace cílových skupin na skupině zařízení */
    public $editDeviceGroupTargetGroups;


    /** @var integer @persistent označena skupina zařízení */
    public $selectDeviceGroup;

    /** @var integer @persistent */
    public $addDeviceGroupToParent;


    /**
     * handle z hlavní šablony, zavření panelu deviceGroup a device
     *
     * @param $id
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleClose($id)
    {
        if ($id == 'deviceGroupEdit') {
            $this->editDeviceGroupTargetGroups = null;
            $this->editDeviceGroup = null;

        } elseif ($id == 'deviceEdit') {
            $this->editDeviceTargetGroups = null;
            $this->editDevice = null;
        }

        $this->payload->scrollTo = "#base";
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups', 'editDeviceGroupForm', 'editDeviceForm']);

    }

    public function handleSetSelectDeviceInNewCampaign($id)
    {
        $translator = $this->translateMessage();

        /** @var DeviceEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceRepository()->find($id)) {
            $title      = $translator->translate('devicePage.management');
            $this->flashMessage($translator->translate("devicePage.device_not_found", null, ['id' => $id]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this');
        }

        $this->campaignFacade->setNewCampaignSelectDevice($id);

        $title      = $translator->translate('devicePage.management');
        $this->flashMessage($translator->translate("devicePage.add_new_campaign_for_device", null, ['device' => $entity->getName()]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('Campaign:');
    }


    public function handleItemsNested($nestedData, $elementId)
    {
        $nestedData = json_decode($nestedData);

        $sortData = array_map(function ($data) {
            return $data->id;
        }, $nestedData);

        $positionedData = array_flip($sortData);

        $rows = $this->getRows();
        $entity = $rows[$elementId];
        $indexRows = array_values($rows);
        $targetRow = $indexRows[$positionedData[$elementId]];

        $targetPosition = $targetRow->position;
        $em = $this->deviceFacade->getEntityManager();

        $entity->position = $targetPosition;
        $em->persist($entity)->flush();

        $this->payload->_nested_success = true;
        $this->ajaxRedirect('this', null, ['items', 'flash']);
    }



    public function handleDetail($id)
    {
        $this->template->toggle_detail = $id;
        $this->payload->_toggle_detail = $id;

        $this->deviceFacade->getDeviceRepository()->setOpenDetailDevice($id);
        $this->ajaxRedirect('this', null, ['items']);
    }


    public function handleSetFilter($active)
    {
        $deviceRepository = $this->deviceFacade->getDeviceRepository();
        $deviceRepository->setFilterActive($active);

        $filter = $deviceRepository->getFilterActive();

        $message = "set";
        if ($filter === "1") {
            $message = "setActives";

        } elseif ( $filter === "0") {
            $message = "setNonActives";

        } elseif ( $filter === null) {
            $message = "setAll";
        }

        $translator = $this->translateMessage();
        $title      = $translator->translate('devicePage.management');
        $this->flashMessage($translator->translate("devicePage.filter.$message"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('this', null, ['devices', 'flash']);
    }


    public function handleToggleActive($did, $checked)
    {
        $deviceRepository = $this->deviceFacade->getDeviceRepository();

        /** @var DeviceEntity $element */
        if ($element = $deviceRepository->find($did)) {

            $this->deviceFacade->setActive($element, $checked);

            $translator = $this->translateMessage();
            $message    = $element->isActive()
                ? $translator->translate("devicePage.device_active", null, ['name' => $element->getName()])
                : $translator->translate("devicePage.device_non_active", null, ['name' => $element->getName()]);

            $title = $translator->translate('devicePage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        }

        if ($deviceRepository->existFilterActive()) {

            if (($filterActive = $deviceRepository->getFilterActive()) !== null) {
                $this->payload->_switchery_redraw = true;
                $this->payload->_filter_toggle = true;
                $this->ajaxRedirect('this', null, ['devices', 'filter', 'flash']);
                return;
            }
        }

        $this->ajaxRedirect('this', null, ['filter', 'flash']);
    }


    public function handleSetDeviceFilterForCampaigns($did)
    {
        $deviceRepository      = $this->deviceFacade->getDeviceRepository();
        $deviceGroupRepository = $this->deviceFacade->getDeviceGroupRepository();

        /** @var DeviceEntity $element */
        if ($element = $deviceRepository->find($did)) {
            if ($deviceGroupRepository->existFilterDeviceGroup()){
                $deviceGroupRepository->clearFilterDeviceGroup();
            }

            $deviceRepository->setFilterDevice([
                intval($did)
            ]);

            $translator = $this->translateMessage();
            $message    = $translator->translate("devicePage.campaigns_on_device", null, ['name' => $element->getName()]);
            $title      = $translator->translate('devicePage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

            $this->ajaxRedirect('Campaign:', null, ['filter', 'flash']);
        }

        $this->ajaxRedirect('this');
    }



    public function actionInit()
    {
        $this->deviceFacade->initPositions();

    }


    public function renderDefault()
    {
        $deviceRepository = $this->deviceFacade->getDeviceRepository();
        $query            = $this->getUserAllowedDevicesQuery();

        $rows = $deviceRepository->fetch($query);

        $total  = $rows->count();
        $active = $nonActive = 0;

        foreach ($rows as $row) {
            if ($row->active) $active++;
            if (!$row->active) $nonActive++;
        }

        $this->template->editDevice = $this->editDevice;
        $this->template->editDeviceGroup = $this->editDeviceGroup;
        $this->template->editDeviceTargetGroups = $this->editDeviceTargetGroups;
        $this->template->editDeviceGroupTargetGroups = $this->editDeviceGroupTargetGroups;
        $this->template->selectDeviceGroup = $this->selectDeviceGroup;

        if ($this->selectDeviceGroup) {
            $this->template->selectDeviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->find($this->selectDeviceGroup);
        }



        $this->template->devices = $this->getRows();

        $this->template->allDeviceCount       = $total;
        $this->template->activeDeviceCount    = $active;
        $this->template->nonActiveDeviceCount = $nonActive;
        $this->template->newDevice            = $this->deviceFacade->isNewDevice();


    }


    /**
     * @deprecated
     *
     * zařadit/vyřadit zařízení do skupiny a podskupin
     *
     * @param $did
     * @param $gid
     * @param $checked
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function handleDeviceSelectInDeviceGroup($did, $gid, $checked)
    {
        if ($did && $gid) {

            /** @var DeviceEntity $device */
            $device = $this->deviceFacade->getDeviceRepository()->find($did);

            /** @var DeviceGroupEntity $deviceGroup */
//            $deviceGroup = $this->deviceFacade->getDeviceGroupRepository()->find($gid);


            $deviceGroup = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                ->addSelect('p')
                ->leftJoin('e.parent', 'p')
                ->where('e.id = ?1')->setParameter(1, $gid)
                ->getQuery()
                ->getOneOrNullResult();


//            die();


//            dump($device);
//            dump($deviceGroup);

            /** @var DeviceGroupEntity[] $childDevicesGroups */
            $childDevicesGroups = $this->deviceFacade->getDeviceGroupRepository()->getChildren($deviceGroup, false, null, 'ASC', true);
//            dump($childDevicesGroups);

            if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                foreach ($childDevicesGroups as $childDevicesGroup) {
                    $childDevicesGroup->addDevice($device);
                }
                $this->deviceFacade->getEntityManager()->persist($childDevicesGroups)->persist($device);

            } else {
                foreach ($childDevicesGroups as $childDevicesGroup) {
                    $childDevicesGroup->removeDevice($device);
                }
                $this->deviceFacade->getEntityManager()->persist($childDevicesGroups);

                /** @var DeviceGroupEntity $parent */
                $parent = $deviceGroup->getParent();

                while ($parent) {
                    $parent->removeDevice($device);
                    $this->deviceFacade->getEntityManager()->persist($parent);
                    $parent = $parent->getParent();
                }
            }

            $uow = $this->deviceFacade->getEntityManager()->getUnitOfWork();
            $uow->computeChangeSets();

            if ($uow->getScheduledCollectionDeletions() || $uow->getScheduledCollectionUpdates()) {
                $translator = $this->translateMessage();

                $this->deviceFacade->getEntityManager()->flush();

                /*
                 * check one device group
                 */
                if ($device->getDevicesGroups()->count() == 0) {
                    $unPlaceDeviceGroup = $this->deviceFacade->getDeviceGroupRepository()->getUserUnPlaceDeviceGroup($this->getUser());

                    $unPlaceDeviceGroup->addDevice($device);
                    $this->deviceFacade->getEntityManager()->persist($unPlaceDeviceGroup)->flush();
                }

                $title      = $translator->translate('devicePage.management');
                $this->flashMessage($translator->translate("devicePage.filter.devicesOnGroup", null, ['name' => $deviceGroup->getName()]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
                $this->ajaxRedirect('this', null, 'flash');
            }

        }




//        dump($did);
//        dump($gid);ASS
//        dump($checked);
//
//        die("ASd");
    }


    private function getUserAllowedDevicesQuery()
    {
        return $this->deviceFacade->getDeviceRepository()->getUserAllowedQuery($this->user);
    }


    /**
     * @return DeviceEntity[]
     */
    public function getRows()
    {
        static $rows = null;

        if (null === $rows) {
            $query = $this->getUserAllowedDevicesQuery();

            $deviceRepository = $this->deviceFacade->getDeviceRepository();

            if ($deviceRepository->existFilterActive()) {
                $filterActive = $deviceRepository->getFilterActive();

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
            $rows = $this->setRows($deviceRepository->fetch($query));
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
     * add/edit device
     *
     * @param $name
     * @return Multiplier
     */
    protected function createComponentDeviceForm($name)
    {
        return new Multiplier(function ($index) use ($name) {

            $form = $this->deviceFormFactory->create();
            $form->setTranslator($this->translator->domain("messages.forms.deviceDetailForm"));
            $form->setFormName($name);

            /** @var DeviceGroupEntity $unPlaceGroupEntity */
            $unPlaceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserUnPlaceDeviceGroup($this->getUser());

            $entity = new DeviceEntity();
            $entity->setUsersGroups($this->userEntity->getGroup());

            $devicesGroups = $this->getDevicesGroups();
            $form->setDevicesGroups($devicesGroups);

            if ($this->deviceFacade->isNewDevice()) {
                $this->deviceFacade->cleanNewDevice();
            }

            if (is_numeric($index)) {
                if (!$entity = $this->deviceFacade->getDeviceRepository()->find($index)) {
                    $entity = (new DeviceEntity())->setUsersGroups($this->userEntity->getGroup());
                    $unPlaceGroupEntity->addDevice($entity);
                }

            } else {
                $unPlaceGroupEntity->addDevice($entity);
            }



            $form->create();
            $form->bootstrap3Render();
            $form->bindEntity($entity);
            $form->onSuccess[] = function (BaseForm $form, $values) use ($index) {

                /** @var DeviceEntity $entity */
                $entity = $form->getEntity();


                /*
                 * add new device to user
                 */
                $userEntity = $this->userEntity;
                $userEntity->addDevice($entity);

                /**
                 * porovnáme value devices s entitou, nesouhlasné device smažeme
                 */
                foreach ($values->devicesGroups as $did) {
                    $dg = $this->getDevicesGroups()[$did];
                    $dg->addDevice($entity);
                }

                foreach ($entity->getDevicesGroups() as $devicesGroup) {
                    if (! in_array($devicesGroup->getId(), (array) $values->devicesGroups)) {
                        $devicesGroup->removeDevice($entity);
                    }
                }

                $translator = $this->translateMessage();

                try {
                    $this->deviceFacade->getEntityManager()->persist($entity)->persist($userEntity)->flush();

                    if ($index == 'new') {
                        $form->setValues([
                        ], true);
                        $this->payload->_un_collapse = "#collapseDeviceForm";
                    }


                    $title   = $translator->translate('devicePage.management');
                    $message = $entity->getId()
                        ? $translator->translate("devicePage.device_added", null, ['name' => $entity->getName()])
                        : $translator->translate("devicePage.device_updated", null, ['name' => $entity->getName()]);

                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

                    $this->editDevice = null;
                    $this->editDeviceGroup = null;
                    $this->addDeviceGroupToParent = null;
                    $this->editDeviceGroupTargetGroups = null;

                    $this->payload->scrollTo = "#base";
                    $this->payload->url = $this->link('this');
                    $this->ajaxRedirect('this', 'deviceGridControl', ['flash', 'addDeviceForm', 'editDeviceForm']);

                } catch (UniqueConstraintViolationException $e) {
                    $title   = $translator->translate('devicePage.management');
                    $message = $translator->translate('devicePage.device_error', $e->getErrorCode(), ['name' => $entity->getSn()]);
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
                    $this->ajaxRedirect('this', null, ['flash']);
                }


            };

            return $form;
        });
    }


    /**
     * @deprecated use createComponentEditDeviceGroupForm instead
     *
     * modal add / edit group
     *
     * @param $name
     * @return \CmsModule\Forms\DeviceGroupForm
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function createComponentAddDeviceGroupForm($name)
    {
        $form = $this->deviceGroupFormFactory->create();
        $form->setTranslator($this->translator->domain("messages.forms.deviceGroupDetailForm"));
        $form->setFormName($name);

        $form->create();
        $form->bootstrap3Render();


        $entity = (new DeviceGroupEntity('Výchozí'))
            ->setUsersGroups($this->userEntity->getGroup());

        $rootEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserRootDeviceGroup($this->getUser());
        $entity->setParent($rootEntity);

        if ($this->editDeviceGroup) {
            if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($this->editDeviceGroup)) {
                $entity = (new DeviceGroupEntity('Výchozí'))
                    ->setParent($rootEntity)
                    ->setUsersGroups($this->userEntity->getGroup());
            }

        } else {
            if ($this->addDeviceGroupToParent) {
                if ($parentGroup = $this->deviceFacade->getDeviceGroupRepository()->find($this->addDeviceGroupToParent)) {
                    $entity->setParent($parentGroup);
                }
            }
        }


        $form->bindEntity($entity);
        $form->onSuccess[] = function (BaseForm $form) {

            /** @var DeviceGroupEntity $entity */
            $entity = $form->getEntity();

            /*
             * add new device group to user
             */
            $userEntity = $this->userEntity;
            $userEntity->addDeviceGroup($entity);

            $this->deviceFacade->getEntityManager()->persist($entity)->persist($userEntity)->flush();

            $this->payload->lastDeviceGroupId = $entity->getId();
            $this->payload->lastDeviceGroupName = $entity->getName();

            $translator = $this->translateMessage();
            $title      = $translator->translate('devicePage.management');
            $message    = $translator->translate('devicePage.group_added', null, ['name' => $entity->getName()]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

            $openDeviceId = $this->deviceFacade->getDeviceRepository()->getOpenDetailDevice();

            $this->template->toggle_detail = $openDeviceId;
            $this->payload->_toggle_detail = $openDeviceId;

//            $this['deviceGroupGridControl']->redrawItem($this->editDeviceGroup, 'e.id');

            $this->editDeviceGroup = null;
            $this->addDeviceGroupToParent = null;
            $this->ajaxRedirect('this', 'deviceGroupGridControl', [/*'items'*/ 'flash']);
//            $this->ajaxRedirect('this', null, [/*'items'*/ 'flash']);

        };

        return $form;
    }


    /**
     * @param $name
     * @return Multiplier
     */
    protected function createComponentDeviceGroupForm($name)
    {
        $self = $this;

        return new Multiplier(function ($index) use ($self, $name) {
            $form = $this->deviceGroupFormFactory->create();
            $form->setTranslator($this->translator->domain("messages.forms.deviceGroupDetailForm"));
            $form->setFormName($name);

            $form->create();
            $form->bootstrap3Render();


            $entity = (new DeviceGroupEntity('Výchozí'))
                ->setUsersGroups($this->userEntity->getGroup());

            $rootEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserRootDeviceGroup($this->getUser());
            $entity->setParent($rootEntity);

            if (is_numeric($index)) {
                if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($index)) {
                    $entity = (new DeviceGroupEntity('Výchozí'))
                        ->setParent($rootEntity)
                        ->setUsersGroups($this->userEntity->getGroup());
                }

            } else {
                if ($this->addDeviceGroupToParent) {
                    if ($parentGroup = $this->deviceFacade->getDeviceGroupRepository()->find($this->addDeviceGroupToParent)) {
                        $entity->setParent($parentGroup);
                    }
                }
            }


            $form->bindEntity($entity);
            $form->onSuccess[] = function (BaseForm $form) {

                /** @var DeviceGroupEntity $entity */
                $entity = $form->getEntity();

                /*
                 * add new device group to user
                 */
                $userEntity = $this->userEntity;
                $userEntity->addDeviceGroup($entity);

                $this->deviceFacade->getEntityManager()->persist($entity)->persist($userEntity)->flush();

                $translator = $this->translateMessage();
                $title      = $translator->translate('devicePage.management');
                $message    = $translator->translate('devicePage.group_added', null, ['name' => $entity->getName()]);
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

//            $this['deviceGroupGridControl']->redrawItem($this->editDeviceGroup, 'e.id');

                $this->editDeviceGroup = null;
                $this->addDeviceGroupToParent = null;
                $this->editDeviceGroupTargetGroups = null;

                $this->payload->scrollTo = "#base";
//                $this->payload->url = $this->link('this');

                $this->ajaxRedirect('this', 'deviceGroupGridControl', ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups', 'editDeviceGroupForm']);
            };

            return $form;

        });
    }


    public function handleDeviceSort()
    {
        $translator = $this->translateMessage();
        $title      = $translator->translate('devicePage.management');
        $message    = "Přesun zařízení není implementováno <em><strong>(feature?)</strong></em>";
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
        $this->ajaxRedirect('this', null, ['flash']);
    }


    public function handleSort($item_id, $prev_id, $next_id, $parent_id)
    {
        Debugger::barDump($_REQUEST);




        $repository = $this->deviceFacade->getDeviceGroupRepository();

//        $this->newTree();
//        $this->resetTree();
//        return;

        if (!Validators::isNumeric($item_id)) {
            throw new InvalidArgumentException("item_id must define");
        }

        /** @var DeviceGroupEntity $item */
        $item     = $repository->find($item_id);

        $translator = $this->translateMessage();
        if ($item->isUnPlace()) {
            $title      = $translator->translate('devicePage.management');
            $message    = $translator->translate("devicePage.group_move_denied", null, ['name' => $item->getName()]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect('this', "deviceGroupGridControl", [/*'devices',*/ 'flash']);
            return;
        }


        /** @var DeviceGroupEntity|null $parent */
        $parent = $parent_id ? $repository->find($parent_id) : null;

        /** @var DeviceGroupEntity|null $next */
        $next = $next_id ? $repository->find($next_id) : null;

        /** @var DeviceGroupEntity|null $prev */
        $prev = $prev_id ? $repository->find($prev_id) : null;

        if ($parent) {
            $item->setParent($parent);
        }

        if ($next) {
            $repository->persistAsPrevSiblingOf($item, $next);

        } elseif ($prev) {
            $repository->persistAsNextSiblingOf($item, $prev);
        }

        if ($parent_id) {

        } else {
//            Debugger::barDump("special");
//            $item->setParent(null);
//            $repository->persistAsFirstChild($item);

//            $repository->getEntityManager()->flush();

//            $repository->recover();
//            $repository->moveDown($item);
//            $repository->reorder($item, 'root');

        }






        /** @var DeviceGroupEntity $next */
//        $next     = $repository->find($next_id);

        /** @var DeviceGroupEntity $prev */
//        $prev     = $repository->find($prev_id);



//        $nextSiblings = $repository->getNextSiblings($item);
//        $prevSiblings = $repository->getPrevSiblings($item);
//
//        Debugger::barDump($nextSiblings);
//        Debugger::barDump($prevSiblings);


//        $moveUpPositions = $this->getUpPositions($prev_id, $next_id, $prevSiblings, $nextSiblings);
//        $moveDownPositions = $this->getDownPositions($prev_id, $next_id, $prevSiblings, $nextSiblings);

//        $repository->persistAsFirstChildOf($item, $next);

//        $item->setParent($parent);

//        $repository->persistAsNextSiblingOf($item, $next);
//        $repository->persistAsPrevSiblingOf($item, $next);
//        $repository->persistAsNextSiblingOf($item, $prev);

//        $repository->persistAsPrevSiblingOf($item, $prev);
//        $repository->getEntityManager()->flush();

//        Debugger::barDump($item);


        $repository->getEntityManager()->flush();


//        $repository->recover();
//        $repository->getEntityManager()->flush();


        /*
                if (!$verify = $repository->verify()) {
                    Debugger::barDump($verify);
                    $repository->recover();
                    $repository->getEntityManager()->flush();
                }
        */


        $title      = $translator->translate('devicePage.management');
        $message    = $translator->translate("devicePage.group_moved", null, ['name' => $item->getName()]);
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DEVICE_EDIT_SUCCESS);
//        $this->ajaxRedirect('this', "deviceGroupGridControl", [/*'devices',*/ 'flash']);


        /*
         * redraw item
         * @todo not complete yet
         */

        $model = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
            ->select('e')
//            ->addSelect("({$subDQL->getDQL()}) as products")
            ->andWhere('e.lvl = :level')->setParameter('level', 0)
            ->addOrderBy('e.lvl')
            ->addOrderBy('e.lft')
            ;

        $dataSource = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
            ->where('e.parent = e.id')
            ->addOrderBy('e.lvl')
            ->addOrderBy('e.lft')
            ->getQuery()
            ->getResult();



        /** @var DataGrid $grid */
        $grid = $this['deviceGroupGridControl'];

        $grid->setDataSource($dataSource);

//        $grid->setDataSource($model);

//        $grid->redrawItem($item_id, 'e.id');
//        $grid->redrawItem(4, 'e.id');
//        $grid->redrawItem(7, 'e.id');

//        $grid->redrawItem(6, 'id' );
//        $grid->redrawItem(4 );
//        $grid->redrawItem(7 );


//        $grid->reload();

//        $this['deviceGroupGridControl']->redrawControl();
//        $this['deviceGroupGridControl']->redrawItem(6, 'e.id');

        $this->ajaxRedirect('this', null, ['flash']);

    }


    /**
     * @return DataGrid
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentDeviceGroupGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $subDQL = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('dge')
            ->select("count(dge.id) as pocet")
            ;

//        $q = $this->deviceFacade->getDeviceGroupRepository()->getUserAllowedQuery($this->getUser());

//        $result = $this->deviceFacade->getDeviceGroupRepository()->fetch($q);


        if ($this->user->isAllowed('Cms:Device', 'listAllDevices')) {
            $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                ->select('e')
//            ->addSelect("({$subDQL->getDQL()}) as products")
                ->andWhere('e.lvl = :level')->setParameter('level', 0)
                ->addOrderBy('e.lvl')
                ->addOrderBy('e.lft')
            ;

        } else {
            $rootDeviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserRootDeviceGroup($this->getUser());

            $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                ->select('e')
                ->andWhere('e.lft > :left')->setParameter('left', $rootDeviceGroupEntity->getLft())
                ->andWhere('e.rgt < :right')->setParameter('right', $rootDeviceGroupEntity->getRgt())
                ->andWhere('e.root = :root')->setParameter('root', $rootDeviceGroupEntity)
                ->andWhere('e.lvl = :level')->setParameter('level', 1)
                ->addOrderBy('e.lvl')
                ->addOrderBy('e.lft')
            ;
        }



//        Debugger::barDump($query->getQuery()->getResult());

//        dump($model);
//        die();

        $grid->setDataSource($query);
        $grid->setTreeView(function ($id) {
            $dataSource = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                ->where('e.parent = :parent')->setParameter('parent', $id)
                ->addOrderBy('e.lvl')
                ->addOrderBy('e.lft')
                ->getQuery()
                ->getResult();

            return $dataSource;

        }, function (DeviceGroupEntity $deviceGroupEntity) {
            return $this->deviceFacade->getDeviceGroupRepository()->childCount($deviceGroupEntity) > 0;
        });

//        $grid->addColumnText('name', 'Název')
//            ->setFilterText();

//        $grid->addColumnText('id', 'Id');

        $grid->addColumnLink('name', 'Název', 'selectDeviceGroup!')
            ->setClass('ajax btn btn-xs btn-default btn-block')
//            ->setRenderer(function (DeviceGroupEntity $deviceGroupEntity) use ($selectDeviceGroup) {
//                $html = Html::el('a')->setText($deviceGroupEntity->getName())->href($this->link('selectDeviceGroup!', ['id' => $deviceGroupEntity->getId()]));
//                $html->addAttributes(['class' => $deviceGroupEntity->getId() == $selectDeviceGroup ? 'ajax btn btn-xs btn-primary btn-block' : 'ajax btn btn-xs btn-default btn-block']);
//                return $html;
//            })
            ->setFilterText();


        $grid->addColumnText('tag', 'Štítek')
            ->addAttributes(['title' => 'Barevné označení skupiny zařízení'])
            ->setAlign('left')
            ->setFitContent()
            ->setSortable()
            ->setRenderer(function (DeviceGroupEntity $row) {
                $html = $row->tag
                    ? Html::el('div')->addAttributes(['class' => "{$row->tag}", 'style' => "width: 60px; height: 25px; "])
                    : Html::el('div')->addAttributes(['style' => "background-color: #F2F2F2; width: 60px; height: 25px; "]);
                return $html;

            });

//        $grid->addColumnText('active', 'Aktivní');

        $selectDeviceGroup = $this->selectDeviceGroup;


        $grid->addAction('editTargetGroups', '', 'editDeviceGroupTargetGroups!')
             ->setRenderCondition(function (DeviceGroupEntity $row) {
                 return !$row->isUnPlace();
             })
             ->setIcon('calendar-plus-o')
             ->setClass('ajax btn btn-xs btn-default');


//        $grid->addAction('calendar', '', 'deleteDeviceGroup!')
//             ->setRenderCondition(function (DeviceGroupEntity $row) {
//                 return !$row->isUnPlace();
//             })
//             ->setIcon('calendar')
//             ->setClass('ajax btn btn-xs btn-default')
//             ->setConfirm(function ($item) {
//                 return "Opravdu chcete smazat skupinu zařízení `{$item->name}`?";
//             });


        $grid->addAction('add', '', 'addDeviceGroup!')
//            ->setRenderer(function () {
//                $el = Html::el('span')
//                    ->addAttributes([
//                        'class' => 'btn btn-xs btn-success',
//                        'data-toggle' => "modal",
//                        'data-target' => '.addGroupModal'
//                    ]);
//                $content = (Html::el('span')
//                    ->setAttribute('class', "fa fa-plus")
//                );
//
//                $el->addHtml($content);
//                return $el;
//            })
            ->setRenderCondition(function (DeviceGroupEntity $row) {
                return !$row->isUnPlace();
            })
            ->setIcon('plus')
            ->setDataAttribute('target', '.addGroupModal')
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.add_new_group'))
            ->setTitle('Přidat novou skupinu zařízení')
            ->setClass('hidden btn btn-xs btn-success')
            ;

        $grid->addAction('edit', '', 'editDeviceGroup!')
            ->setClass(function (DeviceGroupEntity $row) {
                return $row->isUnPlace() ? 'm-l-50 btn btn-xs btn-info ajax' : 'btn btn-xs btn-info ajax';
            })
            ->setIcon('pencil')
            ->setTitle('Editace skupiny zařízení')
//            ->setDataAttribute('target', '.addGroupModal')
//            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.edit_device_group'));



        $grid->addAction('delete', '', 'deleteDeviceGroup!')
            ->setRenderCondition(function (DeviceGroupEntity $row) {
                return !$row->isUnPlace();
            })
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat skupinu zařízení `{$item->name}`?";
            });


        if ($this->user->isAllowed('CmsModule\Forms\DeviceForm', 'new')) {
            $grid->addToolbarButton('addDeviceGroup', 'Přidat skupinu') // trick href, javascript attribute!
                 ->addAttributes([
                     'href'      => 'javascript:void(0)',
                     'title'      => $this->translateMessage()->translate('devicePage.add_new_group'),
                     'data-title' => $this->translateMessage()->translate('devicePage.add_new_group'),

                     'data-toggle'   => "collapse",
                     'data-target'   => "#collapseDeviceGroupForm",
                     'aria-expanded' => "true",
                     'aria-controls' => "collapseDeviceGroupForm"
                 ])
                 ->setClass('btn btn-xs btn-success btn-secondary')
                 ->setIcon('plus');
        };


        $grid->setOuterFilterRendering(false);

//        $grid->addGroupAction('Aktivní')->onSelect[]   = [$this, 'setActives'];

        $grid->setTemplateFile(__DIR__ . "/templates/#custom_datagrid_template.latte");



        $grid->setSortable();


        return $grid;
    }


    /**
     * zobrazení tree skupiny zařízení v modal okně správy zařízení
     *
     * @param $name
     * @return Multiplier|DataGrid
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentDeviceGroupListGridControl($name)
    {
        return new Multiplier(function ($index) use ($name) {

            $grid = new DataGrid();
            $grid->setTranslator($this->translator);


            if ($this->user->isAllowed('Cms:Device', 'listAllDevices')) {
                $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                            ->select('e')
                                            ->andWhere('e.lvl = :level')->setParameter('level', 0)
                                            ->addOrderBy('e.lvl')
                                            ->addOrderBy('e.lft')
                ;

            } else {
                $rootDeviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserRootDeviceGroup($this->getUser());

                $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                            ->select('e')
                                            ->andWhere('e.lft > :left')->setParameter('left', $rootDeviceGroupEntity->getLft())
                                            ->andWhere('e.rgt < :right')->setParameter('right', $rootDeviceGroupEntity->getRgt())
                                            ->andWhere('e.root = :root')->setParameter('root', $rootDeviceGroupEntity)
                                            ->andWhere('e.lvl = :level')->setParameter('level', 1)
                                            ->addOrderBy('e.lvl')
                                            ->addOrderBy('e.lft')
                ;
            }



//        Debugger::barDump($query->getQuery()->getResult()[0]);

            /** @var DeviceGroupEntity[] $deviceGroups */
            $deviceGroups = $query->getQuery()->getResult();


//        Debugger::barDump($deviceGroupEntity);


//        dump($deviceGroupEntity->hasDeviceById(1));




//        dump($query);
//        die();

            $grid->setDataSource($query);
            $grid->setTreeView(function ($id) {
                $dataSource = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                                 ->where('e.parent = :parent')->setParameter('parent', $id)
                                                 ->addOrderBy('e.lvl')
                                                 ->addOrderBy('e.lft')
                                                 ->getQuery()
                                                 ->getResult();

                return $dataSource;

            }, function (DeviceGroupEntity $deviceGroupEntity) {
                return $this->deviceFacade->getDeviceGroupRepository()->childCount($deviceGroupEntity) > 0;
            });

//        $grid->addColumnText('name', 'Název')
//            ->setFilterText();

//        $grid->addColumnText('id', 'Id');




            $grid->addColumnText('name', 'Název')
                 ->addAttributes(['class' => 'btn btn-xs btn-default btn-block']);


//        $grid->addColumnText('active', 'Aktivní');


            $grid->addGroupAction('Aktivní')->onSelect[] = [$this, 'setActives'];


            $grid->setTemplateFile(__DIR__ . "/templates/Device/#datagrid_treelist_template.latte");

            $grid->onRender[] = function (DataGrid $grid) use ($index) {

                $form = $this['deviceForm'][$index];
                $grid->template->getLatte()->addProvider('formsStack', $form);
                $grid->template->_form = $form;
            };

            return $grid;
        });
    }


    /**
     * hlavní správa zařízení
     *
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnStatusException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentDeviceGridControl()
    {

        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $subDQL = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('dge')
            ->select("count(dge.id) as pocet")
        ;


        if ($this->user->isAllowed('Cms:Device', 'listAllDevices')) {
            $query = $this->deviceFacade->getDeviceRepository()->createQueryBuilder('e')
                ->select('e');

        } else {
            $query = $this->deviceFacade->getDeviceRepository()->createQueryBuilder('e')
                ->select('e')
                ->join('e.devicesUsers', 'du')
                ->andWhere('du = :user')->setParameter('user', $this->getUser()->getId());
        }


        if ($this->selectDeviceGroup) {

            /** @var DeviceGroupEntity $deviceGroupEntity */
            $deviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->find($this->selectDeviceGroup);

            $query
                ->join('e.devicesGroups', 'dg')
                ->andWhere('dg = :deviceGroup')->setParameter('deviceGroup', $this->selectDeviceGroup)
                ->orWhere('dg.lft > :left AND dg.rgt < :right AND dg.root = :root')
                ->setParameter('root', $deviceGroupEntity->getRoot())
                ->setParameter('left', $deviceGroupEntity->getLft())
                ->setParameter('right', $deviceGroupEntity->getRgt());
        }


        $grid->setDataSource($query);
/*        $grid->setTreeView(function ($id) {
            $dataSource = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                ->where('e.parent = :parent')->setParameter('parent', $id)
                ->getQuery()
                ->getResult();

            return $dataSource;

        }, function (DeviceGroupEntity $deviceGroupEntity) {

//            dump($deviceGroupEntity);
            return $deviceGroupEntity->parent == false;
        });*/

        $grid->addColumnText('tag', 'Štítek')
            ->addAttributes(['title' => 'Barevné označení zařízení'])
            ->setFitContent()
            ->setSortable()
            ->setRenderer(function (DeviceEntity $row) {
                $html = $row->tag
                    ? Html::el('div')->addAttributes(['class' => "{$row->tag}", 'style' => "width: 60px; height: 25px; "])
                    : Html::el('div')->addAttributes(['style' => "background-color: #F2F2F2; width: 60px; height: 25px; "]);
                return $html;

            });

        $grid->addColumnText('sn', 'SN')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();

        $grid->addColumnText('snRotate', 'SN-rotace')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();

        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();

        $statusList = array('' => 'Vše', '0' => 'Neaktivní', '1' => 'Aktivní');

        $grid->addColumnStatus('active', 'Stav')
            ->setSortable()
            ->setFitContent()
            ->addOption(0, 'Neaktivní')
            ->setClass('btn-default')
            ->endOption()
            ->addOption(1, 'Aktivní')
            ->setIcon('check')
            ->setClass('btn-success')
            ->endOption()
            ->setFilterSelect($statusList);

        $grid->getColumn('active')
            ->onChange[] = function ($id, $new_value) {

            /** @var DeviceEntity $entity */
            $entity = $this->deviceFacade->getDeviceRepository()->find($id);

            $entity->setActive( $new_value);
            $this->deviceFacade->getEntityManager()->persist($entity)->flush();

            if ($this->isAjax()) $this['deviceGridControl']->redrawItem($id); else $this->redirect('this');
        };

        $grid->addColumnText('online', 'Online')
             ->setAlign('center')
             ->setRenderer(function (DeviceEntity $row) {

                 $html = Html::el('span');
                 $html
                     ->setText($row->isOnline() ? 'on ' : 'off ')
                     ->setAttribute('class', $row->isOnline() ? 'label label-success' : 'label label-inverse')
                     ->setAttribute('title', $row->getOnlineText());

                 $icon = Html::el('i')->setAttribute('class', $row->isOnline() ? 'fa fa-check' : 'fa fa-times');
                 $html->addHtml($icon);

                 return $html;
             });


//        $grid->addAction('live', 'Live', 'editDevice!')
//             ->setIcon('eye')
//             ->setDataAttribute('target', '.addDeviceModal')
//             ->setDataAttribute('toggle', 'ajax-modal')
//             ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
//             ->setTitle('Preview')
//             ->setClass('btn btn-xs btn-info');



        $grid->addAction('targetGroup', '', 'editDeviceTargetGroups!')
             ->setIcon('calendar-plus-o')
             ->setTitle('Cílové skupiny')
             ->setClass('ajax btn btn-xs btn-default');


//        $grid->addAction('calendar', '', 'calendar')
//             ->setIcon('calendar')
//             ->setDataAttribute('target', '.addDeviceModal')
//             ->setDataAttribute('toggle', 'ajax-modal')
//             ->setTitle('Kalendář')
//             ->setClass('btn btn-xs btn-default');


        $grid->addAction('edit', '', 'editDevice!')
            ->setIcon('pencil')
//            ->setDataAttribute('target', '.addDeviceModal')
//            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
            ->setTitle($this->translateMessage()->translate('devicePage.editDevice'))
            ->setClass('ajax btn btn-xs btn-info');


        $grid->addAction('delete', '', 'deleteDevice!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat skupinu zařízení `{$item->name}`?";
            });






        $grid->addToolbarButton('addDevice!', 'Přidat zařízení')
            ->addAttributes([
                'href'      => 'javascript:void(0)',
                'title'      => $this->translateMessage()->translate('devicePage.addNewDevice'),
                'data-title' => $this->translateMessage()->translate('devicePage.addNewDevice'),

                'data-toggle'   => "collapse",
                'data-target'   => "#collapseDeviceForm",
                'aria-expanded' => "true",
                'aria-controls' => "collapseDeviceForm"
            ])
            /*->addAttributes([
                'data-target' => '.addDeviceModal',
                'data-toggle' => 'ajax-modal',
                'data-title' => $this->translateMessage()->translate('devicePage.addNewDevice'),
            ])*/
            ->setClass('btn btn-xs btn-success btn-secondary')
            ->setIcon('plus');


        $grid->addToolbarButton('viewAllDevice!', 'Všechna zařízení')
            ->setClass('ajax btn btn-xs btn-info btn-secondary')
            ->setIcon('retweet');


        $grid->setSortable();
        $grid->setSortableHandler('deviceSort!');

        return $grid;

    }



    /**
     * component to edit TargetGroups entities on device
     *
     * @return \Devrun\CmsModule\Controls\DeviceTargetGroupsControl
     */
    protected function createComponentDeviceTargetGroupsControl()
    {
        $control = $this->deviceTargetGroupsControlFactory
            ->create()
            ->setUsersGroup($this->userEntity->getGroup());

        if ($this->editDeviceTargetGroups) {

            /** @var DeviceEntity $deviceEntity */
            $deviceEntity = $this->deviceFacade->getRepository()->find($this->editDeviceTargetGroups);

            $control->setDeviceEdit($deviceEntity);
        }

        if ($this->editDeviceGroupTargetGroups) {

            /** @var DeviceGroupEntity $deviceGroupEntity */
            $deviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->find($this->editDeviceGroupTargetGroups);

            $control->setDeviceGroupEdit($deviceGroupEntity);
        }

        $control->onClose[] = function ($id) {

            if ($id == 'deviceGroupEdit') {
                $this->editDeviceGroupTargetGroups = null;

            } elseif ($id == 'deviceEdit') {
                $this->editDeviceTargetGroups = null;
            }

            $this->payload->scrollTo = "#base";
            $this->payload->url = $this->link('this');
            $this->ajaxRedirect('this', null, ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups']);
        };

        return $control;
    }


    protected function createComponentPlayListControl()
    {
        $control = $this->playListControlFactory->create();


        return $control;
    }


    /**
     * readjust modal deviceForm to empty form
     *
     */
    public function handleAddDevice()
    {
        $this->editDevice = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['editDeviceFormModal']);
    }


    /**
     * readjust modal deviceGroupForm to parent deviceGroup
     *
     * @param $id
     */
    public function handleAddDeviceGroup($id)
    {
        if ($id) {
            if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($id)) {

                $title   = $this->translateMessage()->translate('devicePage.management');
                $message = $this->translateMessage()->translate('devicePage.device_group_not_found', null, ['id' => $entity->getId()]);
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
                $this->ajaxRedirect('this', null, ['flash']);
                return;
            }

            $this->addDeviceGroupToParent = $id;

        } else {
            $this->addDeviceGroupToParent = null;
        }

        $this->editDeviceGroup = null;
        $this->ajaxRedirect('this', null, ['editDeviceGroupFormModal']);
    }

    /**
     * readjust modal deviceGroupForm
     *
     * @param $id
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleEditDeviceGroup($id)
    {
        /** @var DeviceGroupEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_group_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->editDeviceGroup = $id;
        $this->payload->scrollTo = "#snippet--editDeviceGroupForm";
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['editDeviceForm', 'editDeviceGroupForm', 'deviceGroupTargetGroups', 'deviceTargetGroups']);
    }

    /**
     * readjust modal deviceForm
     *
     * @param $id
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleEditDevice($id)
    {
        /** @var DeviceEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->editDevice = $id;
        $this->payload->scrollTo = "#snippet--editDeviceForm";
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['editDeviceForm', 'editDeviceGroupForm', 'deviceGroupTargetGroups', 'deviceTargetGroups']);
    }


    /**
     * readjust targetGroups on Device id
     * edit target groups panel
     *
     * @param $id integer
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleEditDeviceTargetGroups($id)
    {
        /** @var DeviceEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->editDevice = null;
        $this->editDeviceGroupTargetGroups = null;
        $this->editDeviceTargetGroups = $id;

        $this->payload->url = $this->link('this');
        $this->payload->scrollTo = "#snippet--deviceTargetGroups";
        $this->payload->closeModal = true;
        $this->ajaxRedirect('this', null, ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups']);
    }



    /**
     * readjust targetGroups on DeviceGroup id
     * edit target groups panel
     *
     * @param $id integer
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleEditDeviceGroupTargetGroups($id)
    {
        /** @var DeviceGroupEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->editDeviceGroup = null;
        $this->editDeviceTargetGroups = null;
        $this->editDeviceGroupTargetGroups = $id;

        $this->payload->url = $this->link('this');
        $this->payload->scrollTo = "#snippet--deviceGroupTargetGroups";
        $this->payload->closeModal = true;
        $this->ajaxRedirect('this', null, ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups']);
    }



    public function handleDeleteDeviceGroup($id)
    {
        /** @var DeviceGroupEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_group_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->deviceFacade->getEntityManager()->remove($entity)->flush();

        $title   = $this->translateMessage()->translate('devicePage.management');
        $message = $this->translateMessage()->translate('devicePage.device_group_removed', null, ['name' => $entity->getName()]);
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);


        $this->selectDeviceGroup = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', ['deviceGroupGridControl', 'deviceGridControl'], ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups']);
    }

    public function handleDeleteDevice($id)
    {
        /** @var DeviceGroupEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceRepository()->find($id)) {

            $title   = $this->translateMessage()->translate('devicePage.management');
            $message = $this->translateMessage()->translate('devicePage.device_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
            $this->ajaxRedirect('this', null, ['flash']);
            return;
        }

        $this->deviceFacade->getEntityManager()->remove($entity)->flush();

        $title   = $this->translateMessage()->translate('devicePage.management');
        $message = $this->translateMessage()->translate('devicePage.device_removed', null, ['name' => $entity->getName()]);
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);


        $this->selectDeviceGroup = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', ['deviceGroupGridControl', 'deviceGridControl'], ['flash', 'deviceTargetGroups', 'deviceGroupTargetGroups']);
    }


    /**
     * nastaví výběr skupiny zařízení
     *
     * @param $id
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleSelectDeviceGroup($id)
    {
        $this->selectDeviceGroup = $id;

        $this->payload->url = $this->link('this', ['selectDeviceGroup' => $id]);
        $this->ajaxRedirect('this', 'deviceGridControl', 'deviceInGroupName');
    }


    /**
     * nastaví výběr všech zařízení
     */
    public function handleViewAllDevice()
    {
        $this->selectDeviceGroup = null;

        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', 'deviceGridControl', 'deviceInGroupName');
    }



    /**
     * @return DeviceGroupEntity[]|null
     */
    public function getDevicesGroups()
    {
        static $devicesGroups;

        if (null === $devicesGroups) {
            $devicesGroups = $this->deviceFacade->getDeviceGroupRepository()->getAssoc(
                $this->deviceFacade->getDeviceGroupRepository()->fetch(
                    $this->deviceFacade->getDeviceGroupRepository()->getUserAllowedQuery($this->user))->getIterator()
            );
        }

        return $devicesGroups;
    }


}