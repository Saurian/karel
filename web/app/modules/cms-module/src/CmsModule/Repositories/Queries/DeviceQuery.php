<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\DeviceEntity;
use Doctrine\ORM\Query;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;
use Nette\Security\User;

class DeviceQuery extends QueryObject
{

    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];


    public function isActive()
    {
        $this->filter[] = function (QueryBuilder $qb) {
            $qb->andWhere('q.active = true');
        };
        return $this;
    }


    public function isNotActive()
    {
        $this->filter[] = function (QueryBuilder $qb) {
            $qb->andWhere('q.active = false');
        };
        return $this;
    }


    public function inDevices(array $devices)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($devices) {
            $qb->andWhere('q.id IN (:ids)')->setParameter('ids', $devices);
        };
        return $this;
    }


    public function byUser(User $user)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($user) {

            $qb
//                ->addSelect('du')
                ->leftJoin('q.devicesUsers', 'du')
                ->leftJoin('q.deviceGroup', 'dg')
                ->leftJoin('dg.devicesGroupsUsers', 'dgu');

            $qb->andWhere('du.id = :user')
//                ->orWhere('dg.id = :dgIds')->setParameter('dgIds', 1);
                ->orWhere('dgu.id = :user')
                ->setParameter('user', $user->getId());
//                ->orWhere('dg IN (:dgIds)')->setParameter('dgIds', [2,1]);
//                ->orWhere(
//                        $qb->expr()->in( 'dg', $qb->getEntityManager()->createQueryBuilder()
//                            ->select('dg3.id')
//                            ->from(UserEntity::class, 'u3')
//                            ->join('u3.devicesGroups', 'dg3',
//                                \Doctrine\ORM\Query\Expr\Join::WITH,
//                                $qb->expr()->andX(
////                                    $qb->expr()->eq('i2.order', 'd2')
//                                    $qb->expr()->eq('u3.id', ':uid')
//                                )
//                            )
//                            ->getDQL()
//                        )
//                    );


        };
        return $this;
    }

    public function orderByPosition()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addOrderBy("q.position");
        };

        return $this;
    }

    public function doCreateQueryBuilder(Kdyby\Persistence\Queryable $repository)
    {
        return $this->doCreateQuery($repository);
    }


    /**
     * @param \Kdyby\Persistence\Queryable $repository
     *
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicDql($repository);

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        $query = $qb->getQuery();

        /** @var Query $cacheQb */
//        $cacheQb = $query->useResultCache(true, 600, "deviceSN_$id" );

        return $qb;
    }


    protected function doCreateCountQuery(Kdyby\Persistence\Queryable $repository)
    {
        return $this->createBasicDql($repository)->select('COUNT(q.id)');
    }



    private function createBasicDql(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
            ->select('q')->from(DeviceEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


}