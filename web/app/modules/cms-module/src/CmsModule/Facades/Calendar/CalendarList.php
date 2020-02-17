<?php


namespace CmsModule\Facades\Calendar;

use CmsModule\Entities\CalendarEntity;
use CmsModule\Entities\CampaignEntity;

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
//        $unique = $this->getUniqueId($calendarEntity->getCampaign(), $calendarEntity->getFrom(), $calendarEntity->getTo());
        $unique = $this->getTimeKey($calendarEntity);

        $this->calendar[$unique] = $calendarEntity;
        return $this;
    }


    public function getRecord(CampaignEntity $campaignEntity, \DateTime $from, \DateTime $to)
    {
        return isset($this->calendar[$unique = $this->getUniqueId($campaignEntity, $from, $to)])
            ? $this->calendar[$unique]
            : null;
    }





    protected function setPercentage()
    {
        foreach ($this->calendar as $index => $calendarEntity) {
            $key = $calendarEntity->getFrom()->getTimestamp() . $calendarEntity->getTo()->getTimestamp();
            $calendarTimeBlocks[$key][$index] = $calendarEntity->getCampaign();
        }

        $campaignTimes = [];
        foreach ($this->calendar as $index => $calendarEntity) {
            $campaignTimes[$calendarEntity->getCampaign()->getId()][] = $index;
        }

        foreach ($campaignTimes as $id => $campaignTime) {
            $campaignTimes[$id] = array_flip($campaignTime);
        }

        foreach ($calendarTimeBlocks as $calendarTimeBlock) {
            $maxPercentage = 100 / count($calendarTimeBlock);
            $sumPercentage = 0;

            /** @var CampaignEntity[] $calendarTimeBlock */
            foreach ($calendarTimeBlock as $key => $campaignEntity) {

                $percentage = 100;
                if ($campaignEntity->getStrategy()) {
                    $timing = $campaignEntity->getStrategy()->getTiming();
                    $timingCount = count($timing);

                    /** @var integer $campaignCounter campaign counter in calendar */
                    $campaignCounter = $campaignTimes[$campaignEntity->getId()][$key];

                    $campaignCount = count($campaignTimes[$campaignEntity->getId()]);

                    $timingIdx = intval(floor(($campaignCounter / $campaignCount) * $timingCount));
                    $percentage = $maxPercentage * ($timing[$timingIdx] / 100);
                }
                $sumPercentage += $percentage;
                $this->calendar[$key]->setPercentage($percentage);
            }

            /** @var CampaignEntity[] $calendarTimeBlock */
            foreach ($calendarTimeBlock as $key => $campaignEntity) {
                $percentage = ($this->calendar[$key]->getPercentage() / $sumPercentage) * 100;
                $this->calendar[$key]->setPercentage($percentage);
            }
        }
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
        $separatedCalendars = [];
        $sortedCalendars    = $this->getCalendar();

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
        ksort($this->calendar);
        $this->setPercentage();
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
     * @return string FromToPosition @example 20191230150000201912311600001
     */
    private function getTimeKey(CalendarEntity $calendarEntity)
    {
        return $calendarEntity->getFrom()->getTimestamp() . $calendarEntity->getTo()->getTimestamp() . $calendarEntity->getCampaign()->getPosition();
    }


    /**
     * @param CampaignEntity $campaignEntity
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string
     */
    private function getUniqueId(CampaignEntity $campaignEntity, \DateTime $from, \DateTime $to)
    {
        return $from->getTimestamp() . $to->getTimestamp() . $campaignEntity->getPosition();
    }

}
