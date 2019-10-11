<?php


namespace Devrun\Utils;

use DateTime;

/**
 * Class Time
 * @package Devrun\Utils
 */
class Time
{

    /**
     * N ISO-8601 numeric representation of the day of the week [1 for Monday through 7 for Sunday]
     *
     * @param DateTime $dateTime
     * @return int
     */
    public static function getDayOfWeek(DateTime $dateTime)
    {
        return intval($dateTime->format('N'));
    }

    /**
     * return single numeric format [2, 6, 10 ...]
     *
     * @param DateTime $dateTime
     * @return int
     */
    public static function getHour(DateTime $dateTime)
    {
        return intval($dateTime->format('G'));
    }


}