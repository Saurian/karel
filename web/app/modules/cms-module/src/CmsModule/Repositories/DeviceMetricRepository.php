<?php


namespace CmsModule\Repositories;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\MetricEntity;
use CmsModule\Entities\UsersGroupEntity;
use Kdyby\Doctrine\EntityRepository;

class DeviceMetricRepository extends EntityRepository
{

    private $useCache = false;


    /**
     * @deprecated
     *
     * @param UsersGroupEntity $usersGroupEntity
     * @return MetricEntity[]
     */
    public function getUserGroupMetrics(UsersGroupEntity $usersGroupEntity )
    {
        $query = $this->createQueryBuilder('e')
            ->addSelect('s')
            ->leftJoin('e.metricStatistics', 's')
            ->where('e.usersGroup = :group')->setParameter('group', $usersGroupEntity)
            ->getQuery();

        $cacheQb = $query->useResultCache(false, 600, 'metrics' );

        /** @var MetricEntity[] $entities */
        $entities = $cacheQb->getResult();

        $metricEntities = [];
        foreach ($entities as $entity) {
            $metricEntities[$entity->getId()] = $entity;
        }

        return $metricEntities;
    }


    public function getDeviceMetrics(DeviceEntity $deviceEntity)
    {
        $query = $this->createQueryBuilder('e')
                      ->addSelect('tg')
                      ->leftJoin('e.targetGroups', 'tg')
                      ->where('e.device = :device')->setParameter('device', $deviceEntity)
                      ->getQuery();

        $cacheQb = $query->useResultCache($this->useCache, 600, "deviceMetrics_{$deviceEntity->getId()}" );

        /** @var MetricEntity[] $entities */
        $entities = $cacheQb->getResult();
        return $entities;
    }



    public function getDeviceGroupMetrics(DeviceGroupEntity $deviceGroupEntity)
    {
        $query = $this->createQueryBuilder('e')
                      ->addSelect('tg')
                      ->leftJoin('e.targetGroups', 'tg')
                      ->where('e.deviceGroup = :deviceGroup')->setParameter('deviceGroup', $deviceGroupEntity)
                      ->getQuery();

        $cacheQb = $query->useResultCache($this->useCache, 600, "deviceGroupMetrics_{$deviceGroupEntity->getId()}" );

        /** @var MetricEntity[] $entities */
        $entities = $cacheQb->getResult();
        return $entities;

    }



    /**
     * @param bool $useCache
     * @return DeviceMetricRepository
     */
    public function setUseCache(bool $useCache): DeviceMetricRepository
    {
        $this->useCache = $useCache;
        return $this;
    }





}