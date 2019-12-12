<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\UuidV4EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Kdyby\Doctrine\Entities\MagicAccessors;
use DateTime;

/**
 * Class CalendarEntity
 *
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\CalendarRepository")
 * @ORM\Table(name="calendar",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="campaign_idx", columns={"id", "campaign_id"}),
 *  },
 *  indexes={
 *     @ORM\Index(name="from_to_idx", columns={"from", "to"}),
 * })
 *
 * @package CmsModule\Entities
 */
class CalendarEntity
{

    use UuidV4EntityTrait;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @var CampaignEntity
     * @ORM\ManyToOne(targetEntity="CampaignEntity", inversedBy="calendars")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy(value={"ASC"})
     */
    protected $campaign;

    /**
     * @var UsersGroupEntity
     * @ORM\ManyToOne(targetEntity="UsersGroupEntity", inversedBy="calendars")
     */
    protected $usersGroups;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="`from`")
     * @ORM\OrderBy(value={"ASC"})
     */
    protected $from;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="`to`")
     */
    protected $to;

    /**
     * @var integer
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    protected $percentage = 0;


    /**
     * CalendarEntity constructor.
     * @param CampaignEntity $campaign
     * @param UsersGroupEntity $usersGroups
     * @param DateTime $datetime
     */
    public function __construct(CampaignEntity $campaign, UsersGroupEntity $usersGroups, DateTime $datetime, DateTime $to, int $percentage = 0)
    {
        $this->campaign = $campaign;
        $this->usersGroups = $usersGroups;
        $this->from = $datetime;
        $this->to = $to;
        $this->percentage = $percentage;
    }

    /**
     * @return CampaignEntity
     */
    public function getCampaign(): CampaignEntity
    {
        return $this->campaign;
    }

    /**
     * @return UsersGroupEntity
     */
    public function getUsersGroups(): UsersGroupEntity
    {
        return $this->usersGroups;
    }

    /**
     * @return DateTime
     */
    public function getFrom(): DateTime
    {
        return $this->from;
    }

    /**
     * @param DateTime $datetime
     * @return CalendarEntity
     * @throws \Exception
     */
    public function setFrom($datetime): CalendarEntity
    {
        if (is_string($datetime)) {
            $datetime = new \Nette\Utils\DateTime($datetime);
        }

        $this->from = $datetime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTo(): DateTime
    {
        return $this->to;
    }


    /**
     * @param DateTime $dateTime
     * @return CalendarEntity
     * @throws \Exception
     */
    public function setTo($dateTime): CalendarEntity
    {
        if (is_string($dateTime)) {
            $dateTime = new \Nette\Utils\DateTime($dateTime);
        }

        if (!$dateTime) {
            $dateTime = new DateTime("0000-00-00 01:00");
        }

        $this->to = $dateTime;
        return $this;
    }





    /**
     * @return int
     */
    public function getPercentage(): int
    {
        return $this->percentage;
    }





}