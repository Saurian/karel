<?php


namespace CmsModule\Repositories;

use CmsModule\Entities\MetricEntity;
use CmsModule\Entities\UsersGroupEntity;
use Kdyby\Doctrine\EntityRepository;

class MetricRepository extends EntityRepository
{

    /**
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


}