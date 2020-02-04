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
use Doctrine\ORM\NonUniqueResultException;
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

    /** @var int  */
    private $lifetime = 600;

    /** @var bool */
    private $useResultCache = false;


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
     * @param array $names
     * @return array
     */
    public function getIdFromNames(array $names = [])
    {
        $result = [];
        foreach ($names as $device) {
            if ($entity = $this->findOneBy(['name' => $device])) {
                $result[] = $entity->id;
            }
        }

        return $result;
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

        $cacheQb = $query->useResultCache($this->useResultCache, $this->lifetime, $cacheId );
        return $cacheQb->getResult();
    }


    /**
     * return cached result
     *
     * @param QueryBuilder $queryBuilder
     * @param string $cacheId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCachedOneOrNullResult(QueryBuilder $queryBuilder, $cacheId = 'deviceGroups')
    {
        $query = $queryBuilder->getQuery()
                              ->setMaxResults(1);

        $cacheQb = $query->useResultCache($this->useResultCache, $this->lifetime, $cacheId );
        return $cacheQb->getOneOrNullResult();
    }


    public function getUserAllowedQuery(User $user)
    {
        $query = (new DeviceGroupQuery())->byHigherLevelThen(0)->orderByLeft();

        if (!$user->isAllowed('Cms:Device', 'listAllDevices')) {
            if ($user->isAllowed('Cms:Device', 'listUsersGroup')) {
                $query->byUsersGroup($user);

            } else {
                $query->byUser($user);
            }
        }

        return $query;
    }


    /**
     * @todo prověřit po vzoru getUserUnPlaceDeviceGroup
     *
     * return root device group for user
     *
     * @param User $user
     * @return DeviceGroupEntity|null
     */
    public function getUserRootDeviceGroup(User $user)
    {
        try {
            return $this->createQueryBuilder('e')
                        ->join('e.devicesGroupsUsers', 'dgu')
                        ->where('dgu.id = ?1')->setParameter(1, $user->getId())
                        ->andWhere('e.lvl = 0')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();

        } catch (NonUniqueResultException $e) {
            return null;
        }
    }


    /**
     * @param User $user
     * @return DeviceGroupEntity|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserUnPlaceDeviceGroup(User $user)
    {
        return $this->createQueryBuilder('e')
            ->join('e.usersGroups', 'dgu')
            ->join('dgu.users', 'u')
            ->where('u.id = ?1')->setParameter(1, $user->getId())
            ->andWhere('e.unPlace = 1')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }



}