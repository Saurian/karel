<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;


use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Facades\Device\FancyTree;
use CmsModule\Forms\DeviceForm;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceMetricRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\UserRepository;
use Devrun\CmsModule\Controls\IDeviceGroupsTreeControlFactory;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\Http\Session;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

/**
 * Class DeviceFacade
 *
 * @package CmsModule\Facades
 * @method onActivate(DeviceEntity $deviceEntity)
 * @method onGroupActivate(DeviceGroupEntity $deviceEntity)
 */
class DeviceFacade
{
    use SmartObject;

    use PositionableTrait;

    /** @var IDeviceGroupsTreeControlFactory */
    private $deviceGroupsTreeControlFactory;

    /** @var DeviceRepository */
    private $deviceRepository;

    /** @var DeviceGroupRepository */
    private $deviceGroupRepository;

    /** @var DeviceMetricRepository */
    private $deviceMetricRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManager */
    private $em;

    /** @var Session */
    private $session;

    /** @var Callback[] */
    public $onActivate = [];

    /** @var Callback[] */
    public $onGroupActivate = [];

    private $allowedDevices;

    private $allowedDevicesGroups;


    /**
     * DeviceFacade constructor.
     *
     * @param DeviceRepository $deviceRepository
     * @param DeviceGroupRepository $deviceGroupRepository
     * @param DeviceMetricRepository $deviceMetricRepository
     * @param UserRepository $userRepository
     * @param Session $session
     */
    public function __construct(DeviceRepository $deviceRepository, DeviceGroupRepository $deviceGroupRepository, DeviceMetricRepository $deviceMetricRepository,
                                UserRepository $userRepository, Session $session, IDeviceGroupsTreeControlFactory $deviceGroupsTreeControlFactory)
    {
        $this->deviceGroupsTreeControlFactory = $deviceGroupsTreeControlFactory;
        $this->deviceRepository       = $deviceRepository;
        $this->deviceGroupRepository  = $deviceGroupRepository;
        $this->deviceMetricRepository = $deviceMetricRepository;
        $this->userRepository         = $userRepository;
        $this->session                = $session;
        $this->em                     = $deviceRepository->getEntityManager();
    }


    public function getFancyTree()
    {
        return new FancyTree();
    }


    /**
     * @deprecated use deviceRepository->getUserAllowedQuery instead
     *
     * @param User $user
     *
     * @return array
     */
    public function getAllowedDevices(User $user)
    {
        if (null === $this->allowedDevices) {
            $allowedDevices = $user->isAllowed(DeviceForm::class, 'editAllDevices')
                ? $this->deviceRepository->findAll()
                : $this->userRepository->getAssignedDevices($user);

            $this->allowedDevices = $this->entityPairsRows($allowedDevices);
        }

        return $this->allowedDevices;
    }


    /**
     * @deprecated use deviceGroupRepository->getUserAllowedQuery instead
     *
     * @param User $user
     * @return array
     */
    public function getAllowedDevicesGroups(User $user)
    {
        if (null === $this->allowedDevicesGroups) {
            $allowedDevicesGroups = $user->isAllowed(DeviceForm::class, 'editAllDevices')
                ? $this->deviceGroupRepository->findAll()
                : $this->userRepository->getAssignedDevicesGroups($user);

            $this->allowedDevicesGroups = $this->entityPairsRows($allowedDevicesGroups);
        }

        return $this->allowedDevicesGroups;
    }






    private function entityPairsRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->id] = $row;
        }

        return $_rows;
    }

    /**
     * @param DeviceEntity $deviceEntity
     * @throws \Exception
     */
    public function setOnline(DeviceEntity $deviceEntity)
    {
        $deviceEntity->setOnline(new \DateTime());
        $this->em->persist($deviceEntity)->flush();
    }

    public function setActive(DeviceEntity $deviceEntity, $active)
    {
        $deviceEntity->setActive($active);
        $this->em->persist($deviceEntity);

        $this->onActivate($deviceEntity);
        $this->em->flush();
    }


    public function setGroupActive(DeviceGroupEntity $deviceGroupEntity, $active)
    {
        $deviceGroupEntity->setActive($active);
        $this->em->persist($deviceGroupEntity);

        $this->onGroupActivate($deviceGroupEntity);
        $this->em->flush();
    }


    /**
     * @return DeviceRepository
     */
    public function getDeviceRepository()
    {
        return $this->deviceRepository;
    }

    /**
     * @return DeviceGroupRepository
     */
    public function getDeviceGroupRepository()
    {
        return $this->deviceGroupRepository;
    }

    /**
     * @return DeviceMetricRepository
     */
    public function getDeviceMetricRepository(): DeviceMetricRepository
    {
        return $this->deviceMetricRepository;
    }


    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }


    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->deviceRepository;
    }

    /**
     * @return IDeviceGroupsTreeControlFactory
     */
    public function getDeviceGroupsTreeControlFactory(): IDeviceGroupsTreeControlFactory
    {
        return $this->deviceGroupsTreeControlFactory;
    }





    /**
     * @return DeviceGroupEntity root deviceGroup for admin user
     */
    public function createNewDeviceGroupForUser(UserEntity $userEntity)
    {
        $deviceGroupEntity = (new DeviceGroupEntity('Default', $userEntity->getGroup()))
            ->setCreatedBy($userEntity)
            ->setUpdatedBy($userEntity);

        $unClassifyDeviceGroupEntity = (new DeviceGroupEntity('Nezařazené', $userEntity->getGroup()))
            ->setCreatedBy($userEntity)
            ->setUpdatedBy($userEntity);

        $userEntity->addDeviceGroup($deviceGroupEntity);
        $userEntity->addDeviceGroup($unClassifyDeviceGroupEntity);

        $unClassifyDeviceGroupEntity->setParent($deviceGroupEntity)->setUnPlace(true);
        $this->getEntityManager()->persist($unClassifyDeviceGroupEntity)->persist($deviceGroupEntity);

        return $deviceGroupEntity;
    }


    private function getSection()
    {
        return $this->session->getSection('device');
    }


    public function setNewDevice()
    {
        $section = $this->getSection();
        $section->newDevice = true;
    }

    /**
     * @return bool
     */
    public function isNewDevice()
    {
        $section = $this->getSection();
        return isset($section->newDevice);
    }

    public function cleanNewDevice()
    {
        $section = $this->getSection();
        unset($section->newDevice);
    }

    public function setNewDeviceGroup()
    {
        $section = $this->getSection();
        $section->newDeviceGroup = true;
    }

    /**
     * @return bool
     */
    public function isNewDeviceGroup()
    {
        $section = $this->getSection();
        return isset($section->newDeviceGroup);
    }

    public function cleanNewDeviceGroup()
    {
        $section = $this->getSection();
        unset($section->newDeviceGroup);
    }

}