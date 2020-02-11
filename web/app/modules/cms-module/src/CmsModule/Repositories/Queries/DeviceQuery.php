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


    public function withDeviceGroups()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addSelect('dg')
               ->leftJoin('q.devicesGroups', 'dg');
        };
        return $this;
    }


    public function find($id)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($id) {
            $qb->andWhere('q.id = :id')->setParameter('id', $id);
        };
        return $this;
    }

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

            $qb->leftJoin('q.devicesUsers', 'du')
               ->andWhere('du.id = :user')
               ->setParameter('user', $user->getId());

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
     * @return Query|\Doctrine\ORM\QueryBuilder
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
            ->select('q')->from(DeviceEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


}