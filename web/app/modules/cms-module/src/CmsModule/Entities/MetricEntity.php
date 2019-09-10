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
     * @ORM\Column(type="string")
     */
    protected $name;


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






}