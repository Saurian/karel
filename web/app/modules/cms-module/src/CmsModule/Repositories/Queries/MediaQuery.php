<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediaQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\MediumDataEntity;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;

class MediaQuery extends QueryObject
{
    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];




    public function realizedFrom($from)
    {


        $this->filter[] = function (QueryBuilder $qb) use ($from) {
            $qb->andWhere('c.realizedFrom >= :realizedFrom')->setParameter('realizedFrom', $from);
        };
        return $this;
    }



    public function realizedTo($to)
    {


        $this->filter[] = function (QueryBuilder $qb) use ($to) {
            $qb->andWhere('c.realizedTo <= :realizedTo')->setParameter('realizedTo', $to);
        };
        return $this;
    }



    public function inDevice(DeviceEntity $deviceEntity)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($deviceEntity) {
            $qb->andWhere('d = :devices')->setParameter('devices', $deviceEntity->getId());
        };
        return $this;
    }



    public function activeDevice($active)
    {
        $active = (boolean)$active;
        $this->filter[] = function (QueryBuilder $qb) use ($active) {
            $qb->andWhere('d.active = :activeDevice')->setParameter('activeDevice', $active);
        };
        return $this;
    }


    public function activeCampaigns($active)
    {
        $active = (boolean)$active;
        $this->filter[] = function (QueryBuilder $qb) use ($active) {
            $qb->andWhere('c.active = :activeCampaigns')->setParameter('activeCampaigns', $active);
        };
        return $this;
    }



    public function withDevicesCampaigns()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addSelect('c,d')
                ->innerJoin('q.campaign', 'c')
                ->innerJoin('c.devices', 'd');
        };
        return $this;
    }






    /**
     * @param \Kdyby\Persistence\Queryable $repository
     *
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicDql($repository)
            ->addSelect('partial m.{id,type}');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;

    }

    private function createBasicDql(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
            ->select('q')->from(MediumDataEntity::class, 'q')
            ->join('q.medium', 'm')
            ;

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    protected function doCreateCountQuery(Kdyby\Persistence\Queryable $repository)
    {
        return $this->createBasicDql($repository)->select('COUNT(q.id)');
    }


}