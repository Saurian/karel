<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UserQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\UserEntity;
use CmsModule\InvalidArgumentException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;
use Nette\Security\User;

class UserQuery extends QueryObject
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


    public function withDevices()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addSelect('d, dg')
//            $qb->addSelect('d')
                ->join('q.devices', 'd')
//                ->join('q.devices', 'd', Join::WITH, 'q.id = :page')->setParameter('page', 3)
                ->join('d.deviceGroup', 'dg');
//                ->leftJoin('d.deviceGroup', 'd2', Join::WITH, 'q.id = :page');
//                ->leftJoin('q.devices', 'd', Join::WITH, 'q.devices = d.id')
//                ->leftJoin('q.devices', 'd', Join::WITH, 'd.id = :page')->setParameter('page', 1);
//                ->leftJoin('q.devicesGroups', 'dg')
//            ->where('d.id IN (:devices)')->setParameter('devices', [10])
//            ->orWhere('d.deviceGroup IN (:devicesGroups)')->setParameter('devicesGroups', [2]);
        };
        return $this;
    }


    public function byUsersGroup(User $user)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($user) {

            $qb->join('q.group', 'ug')
               ->join('ug.users', 'u')
               ->andWhere('u.id = :usersGroup')
               ->setParameter('usersGroup', $user->getId());

        };
        return $this;
    }


    public function byUser($user)
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException;
        }

        $this->filter[] = function (QueryBuilder $qb) use ($user) {
            $qb->andWhere('q.id = :user')->setParameter('user', $user->getId());
        };
        return $this;
    }


    public function byTest($user)
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException;
        }


        $this->filter[] = function (QueryBuilder $qb) use ($user) {
            $qb
                ->leftJoin('q.devices', 'd')
                ->leftJoin('d.deviceGroup', 'dgg')
                ->leftJoin('q.devicesGroups', 'dg')
                ->leftJoin('d.devicesUsers', 'du')
                ->leftJoin('dgg.devicesGroupsUsers', 'dguu')
                ->leftJoin('dg.devicesGroupsUsers', 'dgu')

//                ->andWhere('dgu.id = :user')
                ->andWhere('du.id = :user')
//                ->andWhere('dg.id = 2')
                ->orWhere('dguu.id = :user')
                ->orWhere('dgu.id = :user')
                ->setParameter('user', $user->id)
//                ->setParameter('uid', 3)

            ;

        };

        return $this;

    }


    public function byDevices($user)
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException;
        }


        $this->filter[] = function (QueryBuilder $qb) use ($user) {




            $em = $qb->getEntityManager();

            $subD2 = $em->createQueryBuilder()
                ->select('d2.id')
                ->from(UserEntity::class, 'u2')
                ->join('u2.devices', 'd2')
                ->andWhere('u2 = :u2')
                ->getQuery()
                ->getDQL();

            $subDG2 = $em->createQueryBuilder()
                ->select('d3.id')
                ->from(UserEntity::class, 'u3')
                ->join('u3.devices', 'd3')
                ->andWhere('u3 = :u2')
                ->getQuery()
                ->getDQL();



//            $expr = $qb->expr();
            $qb
//                ->select(array('DISTINCT i.id', 'i.name', 'o.name'))
//                ->from('Item', 'i')
//                ->join('i.order', 'o')
                ->where(
                    $qb->expr()->in(
                        'd.id',
                        $em->createQueryBuilder()
                            ->select('d2.id')
                            ->from(UserEntity::class, 'u2')
//                            ->join('u2.devicesGroups', 'dg')
                            ->join('u2.devices', 'd2',
                                \Doctrine\ORM\Query\Expr\Join::WITH,
                                $qb->expr()->orX(
//                                    $qb->expr()->eq('d2.deviceGroup', '2'),
                                    $qb->expr()->eq('u2.id', ':uid')
                                )
                            )
                            ->getDQL()
                    )
                )

                ->orWhere(
                    $qb->expr()->in(
                        'd.deviceGroup',
                        $em->createQueryBuilder()
                            ->select('dg3.id')
                            ->from(UserEntity::class, 'u3')
                            ->join('u3.devicesGroups', 'dg3',
                                \Doctrine\ORM\Query\Expr\Join::WITH,
                                $qb->expr()->andX(
//                                    $qb->expr()->eq('i2.order', 'd2')
                                    $qb->expr()->eq('u3.id', ':uid')
                                )
                            )
                            ->getDQL()
                    )
                )


//                ->andWhere($qb->expr()->neq('i.id', '?2'))
//                ->where('d.id IN (:devices)')->setParameter('devices', [10])

//                ->where("d.id IN ($subD2)")//->setParameter('devices', [10])
//                ->orWhere("d.deviceGroup IN ($subDG2)")//->setParameter('devicesGroups', [2])

//                ->orWhere('d.deviceGroup IN (:devicesGroups)')->setParameter('devicesGroups', [2]);
//                ->orderBy('o.orderdate', 'DESC')
                ->setParameter('uid', $user->id)
//                ->setParameter('u2', 3)
//                ->setParameter('u3', 3)
            ;





//            $qb->orWhere('d = q.devices');
//            $qb->orWhere('d.id = :user')->setParameter('user', $user->getId());
        };
        return $this;
    }


    public function withCreatedByUser($user)
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException;
        }

        $this->filter[] = function (QueryBuilder $qb) use ($user) {
            $qb->orWhere('q.createdBy = :createdBy')->setParameter('createdBy', $user->getId());
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
            ->select('q')->from(UserEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}