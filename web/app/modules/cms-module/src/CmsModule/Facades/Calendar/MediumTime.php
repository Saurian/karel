<?php


namespace CmsModule\Facades\Calendar;

use CmsModule\Entities\MediumDataEntity;

/**
 * Class MediumTime
 * @package CmsModule\Facades\Calendar
 */
class MediumTime
{

    /** @var MediumDataEntity */
    private $mediumDataEntity;

    /** @var \DateTime */
    private $from;

    /** @var \DateTime */
    private $to;

    /** @var string */
    private $length;

    /**
     * MediumTime constructor.
     * @param MediumDataEntity $mediumDataEntity
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function __construct(MediumDataEntity $mediumDataEntity, \DateTime $from, \DateTime $to)
    {
        $this->mediumDataEntity = $mediumDataEntity;
        $this->from             = clone $from;
        $this->to               = clone $to;
        $this->setLength($from->diff($to));
    }

    /**
     * @return MediumDataEntity
     */
    public function getMediumDataEntity(): MediumDataEntity
    {
        return $this->mediumDataEntity;
    }

    /**
     * @return \DateTime
     */
    public function getFrom(): \DateTime
    {
        return $this->from;
    }

    /**
     * @return \DateTime
     */
    public function getTo(): \DateTime
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getLength(): string
    {
        return $this->length;
    }


    /**
     * @param string $length
     * @return MediumTime
     */
    protected function setLength(\DateInterval $interval): MediumTime
    {
        $char = "+";

        $length = ($interval->y) ? ($interval->y == 1 ? "{$char}1 year " : "{$char}$interval->y years ") : "";
        $length .= ($interval->m) ? ($interval->m == 1 ? "{$char}1 month " : "{$char}$interval->m months ") : "";
        $length .= ($interval->d) ? ($interval->d == 1 ? "{$char}1 day " : "{$char}$interval->d days ") : "";
        $length .= ($interval->h) ? ($interval->h == 1 ? "{$char}1 hour " : "{$char}$interval->h hours ") : "";
        $length .= ($interval->i) ? ($interval->i == 1 ? "{$char}1 minute " : "{$char}$interval->i minutes ") : "";
        $length .= ($interval->s) ? ($interval->s == 1 ? "{$char}1 second " : "{$char}$interval->s seconds ") : "";

        $this->length = rtrim($length);
        return $this;
    }



}
