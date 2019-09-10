<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\UuidV4EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class CalendarEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="calendar",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="campaign_idx", columns={"id", "campaign_id"}),
 *  })
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
     */
    protected $campaign;






}