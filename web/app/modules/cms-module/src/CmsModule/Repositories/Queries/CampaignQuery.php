<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\MediumDataEntity;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;
use Nette\Security\User;

class CampaignQuery extends QueryObject
{


    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];

    /**
     * @var array of alias inner join
     */
    private $join = [];

    /**
     * @var array of alias
     */
    private $addSelect = [];


    /** @var string alias in result [select q| select q AS alias] */
    private $baseAlias;

    /**
     * CampaignQuery constructor.
     */
    public function __construct($baseAlias = null)
    {
        parent::__construct();
        $this->baseAlias = $baseAlias;
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


    public function allowedByUser(User $user)
    {



    }

    /**
     * @param       $against
     * @param float $match
     * @param null  $mode
     *
     * @return $this
     */
    public function matchByKeywords($against, $match = 0.0, $mode = null)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($against, $match, $mode) {
            if ($mode && (!in_array(strtolower($mode), ['boolean', 'expand']))) {
                $mode = null;
            }

            $qb
                ->addSelect("(MATCH (q.name, q.keywords) AGAINST (:against $mode) + MATCH (md.keywords) AGAINST (:against $mode))  AS HIDDEN matchAlias")
                ->andWhere("(MATCH (q.name, q.keywords) AGAINST (:against $mode) + MATCH (md.keywords) AGAINST (:against $mode) ) > $match")->setParameter('against', $against)
                ->addOrderBy('matchAlias', 'desc');

        };
        return $this;
    }


    public function realizedFrom($from)
    {


        $this->filter[] = function (QueryBuilder $qb) use ($from) {
            $qb->andWhere('q.realizedFrom >= :realizedFrom')->setParameter('realizedFrom', $from);
        };
        return $this;
    }



    public function realizedTo($to)
    {


        $this->filter[] = function (QueryBuilder $qb) use ($to) {
            $qb->andWhere('q.realizedTo <= :realizedTo')->setParameter('realizedTo', $to);
        };
        return $this;
    }



    public function byUser(User $user)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($user) {
            $this->join($qb, 'd', 'devices');
            $this->join($qb, 'dg', 'devicesGroups');

//            $this->addSelect($qb, 'd');
//            $this->addSelect($qb, 'dg');
//            $this->addSelect($qb, 'du');
//            $this->addSelect($qb, 'dgu');

//            $qb->addSelect('du')

            $qb
                ->leftJoin('d.devicesUsers', 'du')
                ->leftJoin('dg.devicesGroupsUsers', 'dgu');

            $qb->andWhere('du.id = :user OR dgu.id = :user')->setParameter('user', $user->getId());
        };
        return $this;
    }


    public function byCampaigns($campaigns)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($campaigns) {
            if (is_array($campaigns)) {
                $qb->andWhere('q.id IN (:ids)')->setParameter('ids', $campaigns);

            } else {
                $qb->andWhere('q.id = :id')->setParameter('id', $campaigns);
            }

        };
        return $this;
    }


    public function withDevices()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->join('q.devices', 'd');
        };
    }


    public function byDevices($devices)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($devices) {
            $this->join($qb, 'd', 'devices');
//            $this->addSelect($qb, 'd');

            $qb->andWhere('d IN (:devices)')->setParameter('devices', $devices);
        };
        return $this;
    }


    /**
     * kampaně na zařízení nebo skupina zařízení obsahuje zařízení
     * [default campaigns in device, campaigns in loop in device]
     *
     * @param $device
     *
     * @return $this
     */
    public function byDeviceOrInDeviceGroups($device)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($device) {
            $qb
                ->leftJoin('q.devices', 'd')
                ->leftJoin('q.devicesGroups', 'dg')
                ->leftJoin('dg.devices', 'dgd');

            $qb->andWhere('d = :device')->setParameter('device', $device->id)
                ->orWhere('dgd = :devices')->setParameter('devices', [$device->id]);
//                ->orWhere('dgd IN (:devices)')->setParameter('devices', [$device->id]);
        };
        return $this;
    }


    public function byDevice($device)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($device) {
            $this->join($qb, 'd', 'devices');
//            $this->addSelect($qb, 'd');

            $qb->andWhere('d = :device')->setParameter('device', $device);
        };
        return $this;
    }

    public function deviceActive($active)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($active) {
            $this->join($qb, 'd', 'devices');
//            $this->addSelect($qb, 'd');

            $qb->andWhere('d.active = :deviceActive')->setParameter('deviceActive', $active);
        };
        return $this;
    }

    public function orDevicesGroups($devicesGroups)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($devicesGroups) {
            $this->join($qb, 'dg', 'devicesGroups');
//            $this->addSelect($qb, 'dg');

            $qb->orWhere('dg.id IN (:devicesGroups)')->setParameter('devicesGroups', $devicesGroups);
        };
        return $this;
    }


    public function withMediaData()
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addSelect('md');
            $qb->leftJoin('q.mediaData', 'md');
        };
        return $this;
    }


    public function withMediaDataCount()
    {
        $this->select[] = function (QueryBuilder $qb) {

            $subCount = $qb->getEntityManager()->createQueryBuilder()
                ->select('COUNT(md.id)')
                ->from(MediumDataEntity::class, 'md')
                ->andWhere('md.campaign = q');

            $qb->addSelect("($subCount) AS mediaDataCount");

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

        return $qb;
    }


    protected function _doCreateCountQuery(Kdyby\Persistence\Queryable $repository)
    {
        return $this->createBasicDql($repository)->select('COUNT(q.id)');
    }



    private function createBasicDql(Kdyby\Persistence\Queryable $repository)
    {
        $selector = $this->baseAlias
            ? "q AS {$this->baseAlias}"
            : 'q';

        $qb = $repository->createQueryBuilder()
            ->select($selector)->from(CampaignEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    /**
     * join table if not joined
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $alias
     */
    private function join(QueryBuilder & $queryBuilder, $alias, $tableName)
    {
        if (!isset($this->join[$alias]))
            $this->join[$alias] = ($queryBuilder->leftJoin("q.$tableName", $alias) == true); // $this->joinDevices = true
    }


    /**
     * addSelect if alias not use yet
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $alias
     */
    private function addSelect(QueryBuilder & $queryBuilder, $alias)
    {
        if (!isset($this->addSelect[$alias])) {
            $this->addSelect[$alias] = ($queryBuilder->addSelect($alias) == true);
        }
    }

}