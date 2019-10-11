<?php


namespace CmsModule\Repositories;

use CmsModule\Entities\ShopEntity;
use Devrun\Utils\Time;
use Kdyby\Doctrine\EntityRepository;

class ShopRepository extends EntityRepository
{


    /**
     * @param \DateTime $dateTime
     * @param ShopEntity $shopEntity
     * @return bool
     */
    public function isTimeInShopTimeRange(\DateTime $dateTime, ShopEntity $shopEntity)
    {
        $shopOpenHour = $shopEntity->getOpenHour();
        $shopCloseHour = $shopEntity->getCloseHour();
        $shopOpenDay = $shopEntity->getOpenDayOfWeek();
        $shopCloseDay = $shopEntity->getCloseDayOfWeek();

        $dayOfWeek = Time::getDayOfWeek($dateTime);
        $hour = Time::getHour($dateTime);

        return $dayOfWeek >= $shopOpenDay && $dayOfWeek <= $shopCloseDay && $hour >= $shopOpenHour && $hour <= $shopCloseHour;
    }



}