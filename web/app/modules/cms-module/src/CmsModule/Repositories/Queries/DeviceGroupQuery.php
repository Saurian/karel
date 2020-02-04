<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceGroupQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\DeviceGroupEntity;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;
use Nette\Security\User;

class DeviceGroupQuery extends QueryObject
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


    public function byUsersGroup(User $user)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($user) {

            $qb->join('q.usersGroups', 'ug')
               ->join('ug.users', 'u')
               ->andWhere('u.id = :usersGroup')
               ->setParameter('usersGroup', $user->getId());

        };
        return $this;
    }


    public function byUser(User $user)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($user) {

            $qb->addSelect('dgu')
                ->innerJoin('q.devicesGroupsUsers', 'dgu')
                ->andWhere('dgu.id = :user')->setParameter('user', $user->getId());
        };
        return $this;
    }


    public function byHigherLevelThen($level = 0)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($level) {
            $qb->andWhere('q.lvl > :level')->setParameter('level', $level);
        };
        return $this;
    }


    public function orderByLeft()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addOrderBy("q.lft");
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


    /**
     * @param Kdyby\Persistence\Queryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
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

        return $qb;
    }


    protected function doCreateCountQuery(Kdyby\Persistence\Queryable $repository)
    {
        return $this->createBasicDql($repository)->select('COUNT(q.id)');
    }


    private function createBasicDql(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
            ->select('q')->from(DeviceGroupEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}