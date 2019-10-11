<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class UsersGroup
 *
 * @ORM\Entity
 * @ORM\Table(name="users_group")
 *
 * @package CmsModule\Entities
 */
class UsersGroupEntity
{
    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var UserEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="UserEntity", mappedBy="group", cascade={"persist"})
     */
    protected $users;

    /**
     * @var TargetGroupEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TargetGroupEntity", mappedBy="usersGroup")
     */
    protected $targets;

    /**
     * @var TargetGroupParamEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TargetGroupParamEntity", mappedBy="usersGroup", cascade={"persist"})
     */
    protected $targetParams;

    /**
     * @var MetricParamEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricParamEntity", mappedBy="usersGroup", cascade={"persist"})
     */
    protected $metricParams;

    /**
     * @var ShopEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ShopEntity", mappedBy="usersGroup", cascade={"persist"})
     */
    protected $shops;

    /**
     * @var MetricEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricEntity", mappedBy="usersGroup", cascade={"persist"})
     */
    protected $metrics;

    /**
     * @var CalendarEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="CalendarEntity", mappedBy="usersGroups", cascade={"persist"})
     */
    protected $calendars;

    /**
     * @var CampaignEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="CampaignEntity", mappedBy="usersGroups", cascade={"persist"})
     */
    protected $campaigns;




    /**
     * UsersGroup constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->targets = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
        $this->calendars = new ArrayCollection();
        $this->metricParams = new ArrayCollection();
    }

    /**
     * @return TargetGroupEntity[]|ArrayCollection
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return MetricParamEntity[]|ArrayCollection
     */
    public function getMetricParams()
    {
        return $this->metricParams;
    }

    /**
     * @param MetricParamEntity $metricParamEntity
     * @return $this
     */
    public function addMetricParam(MetricParamEntity $metricParamEntity)
    {
        if (!$this->metricParams->contains($metricParamEntity)) {
            $this->metricParams->add($metricParamEntity);
        }
        return $this;
    }

    /**
     * @return TargetGroupParamEntity[]|ArrayCollection
     */
    public function getTargetParams()
    {
        return $this->targetParams;
    }




}