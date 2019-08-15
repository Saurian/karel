<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceGroupRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Repositories\Queries\DeviceGroupQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Nette\Security\User;

class DeviceGroupRepository extends EntityRepository implements IFilter
{

    use NestedTreeRepositoryTrait;
    use FilterRepositoryTrait;
    use PostProcessingTrait;


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


    /**
     * return QueryBuilder
     *
     * @param User $user
     * @return Query|\Doctrine\ORM\QueryBuilder
     */
    public function getUserAllowedQueryBuilder(User $user)
    {
        return $this->getUserAllowedQuery($user)->doCreateQueryBuilder($this);
    }


    /**
     * return cached result
     *
     * @param QueryBuilder $queryBuilder
     * @param string $cacheId
     * @return array
     */
    public function getCachedResult(QueryBuilder $queryBuilder, $cacheId = 'deviceGroups')
    {
        $query = $queryBuilder->getQuery();

        $cacheQb = $query->useResultCache(true, 600, $cacheId );
        return $cacheQb->getResult();
    }



    public function getUserAllowedQuery(User $user)
    {
        $query = (new DeviceGroupQuery());

        if (!$user->isAllowed('Cms:Device', 'listAllDevices')) {
            $query->byUser($user);
        }

        return $query;
    }


    /**
     * return root device group for user
     *
     * @param User $user
     * @return DeviceGroupEntity|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserRootDeviceGroup(User $user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.devicesGroupsUsers', 'dgu')
            ->where('dgu.id = ?1')->setParameter(1, $user->getId())
            ->andWhere('e.lvl = 0')
            ->getQuery()
            ->getOneOrNullResult();
    }


    /**
     * @param User $user
     * @return DeviceGroupEntity|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserUnPlaceDeviceGroup(User $user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.devicesGroupsUsers', 'dgu')
            ->where('dgu.id = ?1')->setParameter(1, $user->getId())
            ->andWhere('e.unPlace = 1')
            ->getQuery()
            ->getOneOrNullResult();
    }



}