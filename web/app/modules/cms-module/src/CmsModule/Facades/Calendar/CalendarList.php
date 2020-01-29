<?php


namespace CmsModule\Facades\Calendar;

use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;
use Tracy\Debugger;

/**
 *
 * Class CalendarList
 * @package CmsModule\Facades\Calendar
 */
class CalendarList
{

    /** @var CalendarEntity[] */
    private $calendar;

    /** @var CalendarEntity[] */
    private $compressedCalendar;


    /**
     * @param CalendarEntity $calendarEntity
     * @return $this
     */
    public function addRecord(CalendarEntity $calendarEntity)
    {
        $unique = $this->getUniqueId($calendarEntity->getCampaign(), $calendarEntity->getFrom(), $calendarEntity->getTo());

        $this->calendar[$unique] = $calendarEntity;
        return $this;
    }


    public function getRecord(CampaignEntity $campaignEntity, \DateTime $from, \DateTime $to)
    {
        return isset($this->calendar[$unique = $this->getUniqueId($campaignEntity, $from, $to)])
            ? $this->calendar[$unique]
            : null;
    }


    private function getUniqueId(CampaignEntity $calendarEntity, \DateTime $from, \DateTime $to)
    {
        return $calendarEntity->getId() . $from->getTimestamp() . $to->getTimestamp();
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getCompressedCalendar(): array
    {
        if (null === $this->compressedCalendar) {
            $this->setCompressedCalendar();
        }

        return $this->compressedCalendar;
    }


    /**
     * @param CalendarEntity[] $compressedCalendar
     * @return CalendarList
     * @throws \Exception
     */
    protected function setCompressedCalendar(): CalendarList
    {
        $sortedCalendars = [];
        foreach ($this->calendar as $calendarEntity) {
            $key = $this->getTimeKey($calendarEntity);
            $sortedCalendars[$key] = $calendarEntity;
        }

        ksort($sortedCalendars);

        $separatedCalendars = [];

        foreach ($sortedCalendars as $calendarEntity) {
            $separatedCalendars[$this->getKey($calendarEntity)][] = $calendarEntity;
        }

        $compressedCalendar = [];

        /** @var CalendarEntity[] $separatedCalendar */
        foreach ($separatedCalendars as $separatedCalendar) {

            $_calendarEntity = null;
            foreach ($separatedCalendar as $index => $calendarEntity) {
                if (null == $_calendarEntity) {
                    $_calendarEntity = $calendarEntity;
                }

                if ($_calendarEntity != $calendarEntity && $_calendarEntity->getTo() == $calendarEntity->getFrom()) {
                    $_calendarEntity->setTo($calendarEntity->getTo());

                } elseif ($_calendarEntity != $calendarEntity && $_calendarEntity->getTo() != $calendarEntity->getFrom()) {
                    $compressedCalendar[$key = $this->getKey($_calendarEntity)][] = $_calendarEntity;

                    $_calendarEntity = $calendarEntity;
                }

                if ($index == count($separatedCalendar) - 1) {
                    $compressedCalendar[$key = $this->getKey($_calendarEntity)][] = $_calendarEntity;
                }
            }
        }

        $this->compressedCalendar = [];
        foreach ($compressedCalendar as $item) {
            $this->compressedCalendar = array_merge($this->compressedCalendar, $item);
        }

        return $this;
    }

    /**
     * @return CalendarEntity[]
     */
    public function getCalendar(): array
    {
        return $this->calendar;
    }

    /**
     * @param CalendarEntity[] $calendar
     * @return CalendarList
     */
    public function setCalendar(array $calendar): CalendarList
    {
        $this->calendar = $calendar;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCalendar(): bool
    {
        return !empty($this->calendar);
    }

    /**
     * @param CalendarEntity $calendarEntity
     * @return string Id_DevicesDevicesGroupsIDS_Percentage @example 1_6
     */
    private function getKey(CalendarEntity $calendarEntity)
    {
        $ids = null;

        foreach ($calendarEntity->getDevices() as $device) {
            $ids .= $device->getId();
        }
        foreach ($calendarEntity->getDevicesGroups() as $devicesGroup) {
            $ids .= $devicesGroup->getId();
        }

        return $calendarEntity->getCampaign()->getId() . "_" . $ids . "_" . $calendarEntity->getPercentage();
    }

    /**
     * @param CalendarEntity $calendarEntity
     * @return string FromToId @example 20191230150000201912311600001
     */
    private function getTimeKey(CalendarEntity $calendarEntity)
    {
        return $calendarEntity->getFrom()->getTimestamp() . $calendarEntity->getTo()->getTimestamp() . $calendarEntity->getCampaign()->getId();
    }


}
