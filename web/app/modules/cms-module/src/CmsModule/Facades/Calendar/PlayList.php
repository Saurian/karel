<?php


namespace CmsModule\Facades\Calendar;

use CmsModule\Entities\CalendarEntity;
use Exception;
use Nette\Utils\DateTime;

class PlayList
{

    const DEBUG_MODE = false;

    /** @var CalendarEntity[] */
    private $calendar;

    /** @var array */
    private $calendarSort = [];

    /** @var array */
    private $separateTimes = [];

    /** @var array */
    private $calendarRange = [];

    /** @var DateTime */
    private $from;

    /** @var DateTime */
    private $to;


    /**
     * PlayList constructor.
     * @param CalendarEntity[] $calendar
     * @throws Exception
     */
    public function __construct(array $calendar, DateTime $from = null, DateTime $to = null)
    {
        $this->setFrom($from);
        $this->setTo($to);
        $this->initCalendar($calendar);
    }

    /**
     * @param DateTime|string|null $from
     * @return PlayList
     */
    public function setFrom($from): PlayList
    {
        if (is_string($from)) $from = DateTime::from($from);

        $this->from = $from;
        return $this;
    }

    /**
     * @param DateTime|string|null $to
     * @return PlayList
     */
    public function setTo($to): PlayList
    {
        if (is_string($to)) $to = DateTime::from($to);

        $this->to = $to;
        return $this;
    }

    /**
     * @param CalendarEntity[] $calendar
     * @return PlayList
     * @throws Exception
     */
    protected function initCalendar(array $calendar): PlayList
    {
        /** @var CalendarEntity[] $calendars */
        $calendars = [];

        foreach ($calendar as $calendarEntity) {
            $calendars[$calendarEntity->id] = $calendarEntity;
        }

        if (self::DEBUG_MODE) {
            $this->initForDebugCalendar($calendars);
        }

        $this->calendar = $calendars;

        $separate = [];
        foreach ($calendars as $item) {
            $separate[$item->getFrom()->format('Y-m-d_H:i:s')] = $item->getFrom();
            $separate[$item->getTo()->format('Y-m-d_H:i:s')]   = $item->getTo();

            $this->calendarSort[$item->getFrom()->format('Y-m-d_H:i:s')][] = $item;
        }

        ksort($separate);
        $this->separateTimes = $separate;

        $this->setRangeList($calendars);
        return $this;
    }

    private function initForDebugCalendar(&$calendars)
    {
        if (isset($calendars['88307dc4-8cea-4b20-9914-71948670b6a4'])) {
            $calendars['88307dc4-8cea-4b20-9914-71948670b6a4']->setTo(new \DateTime('2019-10-03 11:00:00'));
        }

        if (isset($calendars['f69d6310-2b9e-44b9-9781-bfbc2cbf8ad8'])) {
            $calendars['f69d6310-2b9e-44b9-9781-bfbc2cbf8ad8']->setTo(new \DateTime('2019-11-04 13:00:00'));
        }
    }

    /**
     *
     *
     * @param array $calendars
     * @return void
     */
    protected function setRangeList(array $calendars)
    {
        /** @var DateTime[] $separateTimes */
        $separateTimes = array_values($this->separateTimes);

        foreach ($calendars as $item) {
            for ($i = 0; $i < count($separateTimes) - 1; $i++) {
                $fromTime = $separateTimes[$i];
                $toTime   = $separateTimes[$i + 1];
                if ($toTime > $item->getTo()) break;
                if ($fromTime >= $item->getFrom() && $toTime <= $item->getTo()) {
                    $ctName = $fromTime->format('Y-m-d-H:i:s' . '_' . $toTime->format('Y-m-d-H:i:s'));

                    if (!isset($this->calendarRange[$ctName])) {
                        $this->calendarRange[$ctName] = new RangeList($fromTime, $toTime);
                    }

                    /** @var RangeList $rangeList */
                    $rangeList = $this->calendarRange[$ctName];
                    $rangeList->addCalendar($item);
                }
            }
        }
    }

    /**
     * @return MediumTime[]
     */
    public function createMediumList()
    {
        $lists = [];

        /** @var RangeList $rangeList */
        foreach ($this->calendarRange as $rangeList) {

            $from = $rangeList->getFromTime();
            $to   = $rangeList->getToTime();

            $current = clone $from;

            while ($current < $to) {
                $someMedium = false;

                foreach ($rangeList->getCalendars() as $calendarEntity) {

                    foreach ($calendarEntity->getCampaign()->getMediaData() as $mediumDataEntity) {
                        $someMedium = true;

                        $time = $mediumDataEntity->getTime()
                            ? $mediumDataEntity->getTime()
                            : '0 second';

                        $toTime = clone $current;
                        $toTime->modify($time);

                        if ($toTime > $to) {
                            $toTime = $to;
                        }

                        if ($current >= $from && $current < $to) {

//                        if (self::DEBUG_MODE && count($lists) < 300)
                            $lists[] = new MediumTime($mediumDataEntity, $current, $toTime);

                        } else break;

                        $current->modify($time);
                    }

                }

                if (!$someMedium) break;
            }
        }

        /*
         * if declared from / to, do filter
         */
        $this->filter($lists);

        return $lists;
    }



    private function filter(&$lists)
    {
        if ($this->from || $this->to) {
            $filtered = array_filter($lists, function (MediumTime $list) {
                $result = null;
                if ($this->from) {
                    $result = $this->from < $list->getTo();
                }
                if (($result === null || $result) && $this->to) {
                    $result = $list->getFrom() < $this->to;
                }

                return $result;
            });

            $lists = $filtered;
        }
    }


}