<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Repositories\Queries\DeviceQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

class DeviceRepository extends EntityRepository implements IFilter
{

    const SESSION_NAME = 'deviceFilter';

    use PostProcessingTrait;
    use FilterRepositoryTrait;

    /** @var int  */
    private $lifetime = 600;

    /** @var bool */
    private $useResultCache = false;


    /** @var DeviceEntity[] @internal */
    private $assocDevices;


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
    public function getCachedResult(QueryBuilder $queryBuilder, $cacheId = 'device')
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
     * @return mixed|null
     */
    public function getCachedOneOrNullResult(QueryBuilder $queryBuilder, $cacheId = 'device')
    {
        $query = $queryBuilder->getQuery();

        $cacheQb = $query->useResultCache($this->useResultCache, $this->lifetime, $cacheId );
        try {
            return $cacheQb->getOneOrNullResult();

        } catch (NonUniqueResultException $e) {
            return null;
        }
    }



    /**
     * @param User $user
     * @return DeviceQuery
     */
    public function getUserAllowedQuery(User $user)
    {
        $query = (new DeviceQuery());

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
     * @param $id
     * @return mixed
     */
    public function findDeviceById($id)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where("e.sn = :deviceSN")->setParameter('deviceSN' , $id);

        return $this->getCachedOneOrNullResult($queryBuilder, "deviceSN_$id");
    }


    public function findByIdWithDevicesGroups($id)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->addSelect('dg')
            ->leftJoin("e.devicesGroups", 'dg')
            ->where("e.id = :id")->setParameter('id', $id);

        return $this->getCachedOneOrNullResult($queryBuilder, "device_$id");
    }


    /**
     * @param User $user
     * @return array|DeviceEntity[]
     */
    public function getAssocDevicesByUser(User $user)
    {
        if (null === $this->assocDevices) {
            $this->assocDevices = $this->getAssoc($this->fetch($this->getUserAllowedQuery($user))->getIterator());
        }

        return $this->assocDevices;
    }









}