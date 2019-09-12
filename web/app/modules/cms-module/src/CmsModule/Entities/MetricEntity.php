<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class MetricEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\MetricRepository")
 * @ORM\Table(name="metric",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="identify_idx", columns={"shop_id", "target_group_id", "metric_param_id"}),
 *  },
 *  indexes={
 *      @ORM\Index(name="name_idx", columns={"name"}),
 *  })
 *
 * @package CmsModule\Entities
 */
class MetricEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @example Office people
     *
     * @var string
     * @ORM\Column(type="string", name="`name`")
     */
    protected $name;

    /**
     * @var UsersGroupEntity
     * @ORM\ManyToOne(targetEntity="UsersGroupEntity", inversedBy="metrics")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $usersGroup;

    /**
     * @var ShopEntity
     * @ORM\ManyToOne(targetEntity="ShopEntity", inversedBy="metrics")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $shop;

    /**
     * @var TargetGroupEntity
     * @ORM\ManyToOne(targetEntity="TargetGroupEntity", inversedBy="metrics")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $targetGroup;

    /**
     * @var MetricParamEntity
     * @ORM\ManyToOne(targetEntity="MetricParamEntity", inversedBy="metrics")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $metricParam;

    /**
     * @var MetricStatisticEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricStatisticEntity", mappedBy="metric")
     */
    protected $metricStatistics;

    /**
     * MetricEntity constructor.
     * @param MetricStatisticEntity[]|ArrayCollection $metricStatistics
     */
    public function __construct()
    {
        $this->metricStatistics = new ArrayCollection();
    }


    /**
     * @return MetricStatisticEntity[]|ArrayCollection
     */
    public function getMetricStatistics()
    {
        return $this->metricStatistics;
    }

    /**
     * @param UsersGroupEntity $usersGroup
     * @return MetricEntity
     */
    public function setUsersGroup(UsersGroupEntity $usersGroup): MetricEntity
    {
        $this->usersGroup = $usersGroup;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return UsersGroupEntity
     */
    public function getUsersGroup(): UsersGroupEntity
    {
        return $this->usersGroup;
    }

    /**
     * @return ShopEntity
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return TargetGroupEntity
     */
    public function getTargetGroup()
    {
        return $this->targetGroup;
    }

    /**
     * @return MetricParamEntity
     */
    public function getMetricParam()
    {
        return $this->metricParam;
    }






}