<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Repositories\Queries\DeviceQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

class DeviceRepository extends EntityRepository implements IFilter
{

    const SESSION_NAME = 'deviceFilter';

    use PostProcessingTrait;
    use FilterRepositoryTrait;


    public function existFilterDevice()
    {
        $section = $this->getSection();
        return isset($section->device);
    }

    public function getFilterDevice()
    {
        if (!$this->existFilterDevice()) return null;
        $section = $this->getSection();
        return $section->device;
    }

    public function setFilterDevice($device)
    {
        $section = $this->getSection();
        $section->device = $device;
    }

    public function addFilterDevice($device)
    {
        $section = $this->getSection();
        $section->device[$device] = $device;
    }

    public function clearFilterDevice($device)
    {
        $section = $this->getSection();
        unset($section->device[$device]);
    }

    public function clearFilterDevices()
    {
        $section = $this->getSection();
        unset($section->device);
    }

    public function getOpenDetailDevice()
    {
        $section = $this->getSection();
        return isset($section->openDetailDevice) ? $section->openDetailDevice : null;
    }

    public function setOpenDetailDevice($id)
    {
        $section = $this->getSection();
        return $section->openDetailDevice = $id;
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
    public function getCachedResult(QueryBuilder $queryBuilder, $cacheId = 'device')
    {
        $query = $queryBuilder->getQuery();

        $cacheQb = $query->useResultCache(true, 600, $cacheId );
        return $cacheQb->getResult();
    }



    /**
     * @param User $user
     * @return DeviceQuery
     */
    public function getUserAllowedQuery(User $user)
    {
        $query = (new DeviceQuery());

        if (!$user->isAllowed('Cms:Device', 'listAllDevices')) {
            $query->byUser($user);
        }

        return $query;
    }


    public function findDevice($id)
    {
        $query = $this->createQueryBuilder('e')
            ->where("e.sn = :deviceSN")->setParameter('deviceSN' , $id)
            ->getQuery();

        /** @var Query $cacheQb */
        $cacheQb = $query->useResultCache(true, 600, "deviceSN_$id" );
        return $cacheQb->getOneOrNullResult();
    }










}