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


}
