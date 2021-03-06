<?php


namespace CmsModule\Repositories\Queries;

use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\UsersGroupEntity;
use Kdyby;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;

class CalendarQuery extends QueryObject
{

    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];


    /*
     * misc
     */
    public function orderByFromTo(): CalendarQuery
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addOrderBy('q.from')
               ->addOrderBy('q.to');
        };

        return $this;
    }

    public function orderByCampaign(): CalendarQuery
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->addOrderBy('campaign.position');
        };

        return $this;
    }



    // ------------------------------------------------------------------------------------------------------

    /*
     * selects
     */

    /**
     * @return CalendarQuery
     */
    public function withCampaigns(): CalendarQuery
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->join('q.campaign', 'campaign')
               ->addSelect('campaign');
        };

        /*
         * with partial is more difficulty to ram
        $this->onPostFetch[] = function (QueryObject $_, Kdyby\Persistence\Queryable $repository, \Iterator $iterator) {
            $ids = iterator_to_array($iterator);

            $repository->createQueryBuilder()
                       ->select('PARTIAL q.{id}, campaign')
                       ->from(CalendarEntity::getClassName(), 'q')
                       ->leftJoin('q.campaign', 'campaign')
                       ->andWhere('q.id IN (:calendars)')->setParameter('calendars', $ids)
                       ->getQuery()->getResult();
        };
        */

        return $this;
    }


    public function withDevices(): CalendarQuery
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->leftJoin('q.devices', 'device')
               ->addSelect('device');
        };

        return $this;
    }


    public function withDevicesGroups(): CalendarQuery
    {
        $this->select[] = function (QueryBuilder $qb) {
            $qb->leftJoin('q.devicesGroups', 'devicesGroups')
               ->addSelect('devicesGroups');
        };

        return $this;
    }



    /*
     * filters
     */


    /**
     * @param int $usersGroup
     * @return $this
     */
    public function byUsersGroup(UsersGroupEntity $usersGroup)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($usersGroup) {
            $qb->andWhere('q.usersGroups = :usersGroup')->setParameter('usersGroup', $usersGroup);
        };
        return $this;
    }


    /**
     * @param string $sn
     * @return $this
     */
    public function byCampaignDeviceSn(string $sn)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($sn) {
            $qb->join('campaign.devices', 'device')
               ->andWhere('device.sn = :device')->setParameter('device', $sn);
        };
        return $this;
    }


    public function byDeviceSn(string $sn)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($sn) {
            $qb->leftJoin('q.devices', 'device')
//               ->addSelect('device')
               ->orWhere('device.sn = :device and device.active = true')->setParameter('device', $sn)
//               ->andWhere('deviceGroupDevices.sn = :device')->setParameter('device', $sn)
            ;
        };
        return $this;
    }


    public function byDeviceGroupSn(string $sn)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($sn) {
            $qb->leftJoin('q.devicesGroups', 'calendarDG')
               ->leftJoin('calendarDG.children', 'calendarDGChildren')
               ->leftJoin('calendarDG.devices', 'calendarDevices')
               ->leftJoin('calendarDGChildren.devices', 'calendarChildrenDevices')
               ->orWhere('calendarChildrenDevices.sn = :calendarChildrenDeviceSN and calendarChildrenDevices.active = true')->setParameter('calendarChildrenDeviceSN', $sn)
               ->andWhere('calendarDGChildren.lft > calendarDG.lft')
               ->andWhere('calendarDGChildren.rgt < calendarDG.rgt')
               ->andWhere('calendarDGChildren.root = calendarDG.root')
            ;
        };
        return $this;
    }


    /**
     * @param bool $active
     * @return $this
     */
    public function deviceActive(bool $active)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($active) {
            $qb->andWhere('device.active = :active')->setParameter('active', $active);
        };
        return $this;
    }


    /**
     * @param bool $active
     * @return $this
     */
    public function campaignActive(bool $active)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($active) {
            $qb->andWhere('campaign.active = :active')->setParameter('active', $active);
        };
        return $this;
    }


    /**
     * @return $this
     */
    public function inCampaignTimeRange()
    {
        $this->filter[] = function (QueryBuilder $qb) {
            $qb->andWhere('q.from >= campaign.realizedFrom');
            $qb->andWhere('q.to <= campaign.realizedTo');
        };
        return $this;
    }


    /**
     * @param $realizedFrom
     * @return $this
     */
    public function betweenFromTo($realizedFrom, $realizedTo)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($realizedFrom, $realizedTo) {
            $qb->andWhere('(q.from between :realizedFrom and :realizedTo) or (q.to between :realizedFrom and :realizedTo)')
               ->andWhere('q.to > :realizedFrom')
               ->setParameter('realizedFrom', $realizedFrom)
               ->setParameter('realizedTo', $realizedTo);
        };
        return $this;
    }


    /**
     * @param $realizedFrom
     * @return $this
     */
    public function realizedFrom($realizedFrom)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($realizedFrom) {
            $qb->andWhere('(q.from >= :realizedFrom) or (q.to >= :realizedFrom)')->setParameter('realizedFrom', $realizedFrom);
        };
        return $this;
    }


    public function realizedTo($realizedTo)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($realizedTo) {
            $qb->andWhere('(q.to <= :realizedTo) or (q.from <= :realizedTo)')->setParameter('realizedTo', $realizedTo);
        };
        return $this;
    }



    public function doCreateQueryBuilder(Kdyby\Persistence\Queryable $repository)
    {
        return $this->doCreateQuery($repository);
    }


    /**
     * @param \Kdyby\Persistence\Queryable $repository
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


    private function createBasicDql(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
                         ->select('q')
                         ->from(CalendarEntity::class, 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    protected function _doCreateCountQuery(Kdyby\Persistence\Queryable $repository)
    {
        return $this->createBasicDql($repository)->select('COUNT(q.id)');
    }


}