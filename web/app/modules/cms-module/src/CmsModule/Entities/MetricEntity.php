<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class MetricEntity
 * @ORM\Entity
 * @ORM\Table(name="metric")
 *
 * @package CmsModule\Entities
 */
class MetricEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;


    /**
     * @var string
     * @ORM\Column(type="time")
     */
    protected $blockTime;

    /**
     * @var string
     * @ORM\Column(type="smallint")
     */
    protected $blockDayOfWeek;

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
     * @var string
     * @ORM\Column(type="string")
     */
    protected $value;



}