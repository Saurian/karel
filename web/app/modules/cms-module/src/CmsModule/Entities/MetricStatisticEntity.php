<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class MetricDataEntity
 * @ORM\Entity
 * @ORM\Table(name="metric_statistic",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="metric_block_idx", columns={"metric_id", "block_day", "block_time"}),
 *  },
 *  indexes={
 *      @ORM\Index(name="block_day_time_idx", columns={"block_time", "block_day"}),
 * })
 *
 * @package CmsModule\Entities
 * @method getT600()
 * @method getT700()
 * @method getT800()
 * @method setT600()
 * @method setT700()
 * @method setT800()
 */
class MetricStatisticEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @var MetricEntity
     * @ORM\ManyToOne(targetEntity="MetricEntity", inversedBy="metricStatistics")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $metric;


    /**
     * @var string
     * @ORM\Column(type="time")
     */
    protected $blockTime;

    /**
     * @var string
     * @ORM\Column(type="smallint")
     */
    protected $blockDay;

    /** @var int @ORM\Column(type="smallint", nullable=true) */
    protected $t600;

    /** @var int @ORM\Column(type="smallint", nullable=true) */
    protected $t700;

    /** @var int @ORM\Column(type="smallint", nullable=true) */
    protected $t800;


    /**
     * @var int
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $value;

    /**
     * MetricStatisticEntity constructor.
     * @param MetricEntity $metric
     */
    public function __construct(MetricEntity $metric)
    {
        $this->metric = $metric;
    }


}