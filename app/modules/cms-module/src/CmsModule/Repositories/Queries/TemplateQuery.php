<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2018
 *
 * @file    TemplateQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\TemplateEntity;
use CmsModule\InvalidArgumentException;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;
use Nette\Security\User;

class TemplateQuery extends QueryObject
{

    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];


    public function byUser($user)
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException();
        }

        $this->filter[] = function (QueryBuilder $qb) use ($user) {
            $em = $qb->getEntityManager();

            $qb->leftJoin('q.users', 'u')
                ->leftJoin('u.devices', 'ud')
                ->leftJoin('ud.devicesUsers', 'udu')
                ->leftJoin('ud.deviceGroup', 'udg')

                ->andWhere('u.id = :user')
                ->orWhere('udu.id = :user')
                ->orWhere(
                    $qb->expr()->in(
                        'udg.id',
                        $em->createQueryBuilder()
                            ->select('dg.id')
                            ->from(DeviceGroupEntity::class, 'dg')
                            ->join('dg.devicesGroupsUsers', 'dgu2',
                                \Doctrine\ORM\Query\Expr\Join::WITH,
                                $qb->expr()->orX(
                                    $qb->expr()->eq('dgu2.id', ':user')
                                )
                            )
                            ->getDQL()
                    )

                )
                ->setParameter('user', $user->getId());
        };
        return $this;
    }


    /**
     * @param Kdyby\Persistence\Queryable $repository
     *
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder(Kdyby\Persistence\Queryable $repository)
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
            ->select('q')->from(TemplateEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }



}