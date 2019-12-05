<?php


namespace CmsModule\Facades\Calendar;


use CmsModule\Entities\CalendarEntity;

class RangeList
{

    /** @var \DateTime */
    private $fromTime;

    /** @var \DateTime */
    private $toTime;

    /** @var CalendarEntity[] */
    private $calendars = [];

    /**
     * RangeList constructor.
     * @param \DateTime $fromTime
     * @param \DateTime $toTime
     */
    public function __construct(\DateTime $fromTime, \DateTime $toTime)
    {
        $this->fromTime = $fromTime;
        $this->toTime   = $toTime;
    }


    /**
     * @param CalendarEntity $calendarEntity
     * @return $this
     */
    public function addCalendar(CalendarEntity $calendarEntity)
    {
        $this->calendars[] = $calendarEntity;
        return $this;
    }

    /**
     * @return CalendarEntity[]
     */
    public function getCalendars(): array
    {
        return $this->calendars;
    }

    /**
     * @return \DateTime
     */
    public function getFromTime(): \DateTime
    {
        return $this->fromTime;
    }

    /**
     * @return \DateTime
     */
    public function getToTime(): \DateTime
    {
        return $this->toTime;
    }






}