<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceGroupRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Repositories\Queries\DeviceGroupQuery;
use Gedmo\Tree\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Nette\Security\User;

class DeviceGroupRepository extends EntityRepository implements IFilter
{

    use NestedTreeRepositoryTrait;
    use FilterRepositoryTrait;


    const SESSION_NAME = 'deviceGroupFilter';


    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->initializeTreeRepository($em, $class);
    }




    public function existFilterDeviceGroup()
    {
        $section = $this->getSection();
        return isset($section->deviceGroup);
    }

    public function getFilterDeviceGroup()
    {
        if (!$this->existFilterDeviceGroup()) return null;
        $section = $this->getSection();
        return $section->deviceGroup;
    }

    public function setFilterDeviceGroup($deviceGroup)
    {
        $section = $this->getSection();
        $section->deviceGroup = $deviceGroup;
    }

    public function clearFilterDeviceGroup()
    {
        $section = $this->getSection();
        unset($section->deviceGroup);
    }


    public function getOpenDetailDeviceGroup()
    {
        $section = $this->getSection();
        return isset($section->openDetailDeviceGroup) ? $section->openDetailDeviceGroup : null;
    }

    public function setOpenDetailDeviceGroup($id)
    {
        $section = $this->getSection();
        return $section->openDetailDeviceGroup = $id;
    }


    public function getUserAllowedQuery(User $user)
    {
        $query = (new DeviceGroupQuery());

        if (!$user->isAllowed('Cms:Device', 'listAllDevices')) {
            $query->byUser($user);
        }

        return $query;
    }





}