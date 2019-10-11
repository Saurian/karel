<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Devrun\Doctrine\Entities\UuidV4EntityTrait;
use Devrun\Utils\Debugger;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\DateTime;

/**
 * Class MetricDataEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\MetricStatisticRepository")
 * @ORM\Table(name="metric_statistic",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="metric_block_idx", columns={"metric_id", "block_day", "block_time"}),
 *  },
 *  indexes={
 *      @ORM\Index(name="block_day_time_idx", columns={"block_time", "block_day"}),
 * })
 *
 * @package CmsModule\Entities
 */
class MetricStatisticEntity
{

    use UuidV4EntityTrait;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @var MetricEntity
     * @ORM\ManyToOne(targetEntity="MetricEntity", inversedBy="metricStatistics", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $metric;


    /**
     * @var integer
     * @ORM\Column(type="smallint")
     */
    protected $blockDay;


    /**
     * @var DateTime
     * @ORM\Column(type="time")
     */
    protected $blockTime;




    /**
     * @var int|null
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

    /**
     * @return DateTime
     */
    public function getBlockTime()
    {
        return $this->blockTime;
//        return $this->blockTime ? $this->blockTime->format('H') : null;
    }

    public function getBlockHour()
    {
        return intval($this->blockTime->format('G'));
    }


    /**
     * @param DateTime|integer $blockTime
     * @return MetricStatisticEntity
     */
    public function setBlockTime($blockTime): MetricStatisticEntity
    {
        if (is_numeric($blockTime)) {
            $blockTime = DateTime::createFromFormat('H', $blockTime);
        }

        $blockTime->setDate(1970, 1, 1);

        if ($this->blockTime != $blockTime) $this->blockTime = $blockTime;
        return $this;
    }

    /**
     * @param int $blockDay
     * @return MetricStatisticEntity
     */
    public function setBlockDay(int $blockDay): MetricStatisticEntity
    {
        $this->blockDay = $blockDay;
        return $this;
    }

    /**
     * @return int
     */
    public function getBlockDay(): int
    {
        return $this->blockDay;
    }

    /**
     * @param int $value
     * @return MetricStatisticEntity
     */
    public function setValue($value): MetricStatisticEntity
    {
        $this->value = $value ? intval($value) : null;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getValue()
    {
        return $this->value;
    }








}