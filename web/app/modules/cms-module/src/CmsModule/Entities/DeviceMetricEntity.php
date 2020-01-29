<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Devrun\Doctrine\Entities\UuidV4EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\DateTime;

/**
 * Class DeviceMetricEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\DeviceMetricRepository")
 * @ORM\Table(name="device_metric",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="device_metric_idx", columns={"device_id", "device_group_id", "block_day", "block_time"}),
 *  },
 *  indexes={
 *      @ORM\Index(name="block_day_time_idx", columns={"block_time", "block_day"}),
 * })
 *
 * @package CmsModule\Entities
 */
class DeviceMetricEntity
{

    use UuidV4EntityTrait;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @var DeviceEntity
     * @ORM\ManyToOne(targetEntity="DeviceEntity", inversedBy="metrics", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $device;


    /**
     * @var DeviceGroupEntity
     * @ORM\ManyToOne(targetEntity="DeviceGroupEntity", inversedBy="metrics", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $deviceGroup;

    /**
     * @var TargetGroupEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="TargetGroupEntity", inversedBy="devicesMetrics")
     * @ORM\JoinTable(name="devices_target_groups")
     */
    protected $targetGroups;


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
     * DeviceMetricEntity constructor.
     * @param $blockDay
     * @param $blockTime
     * @param DeviceEntity|null $device
     * @param DeviceGroupEntity|null $deviceGroup
     */
    public function __construct($blockDay, $blockTime, DeviceEntity $device = null, DeviceGroupEntity $deviceGroup = null)
    {
        $this->targetGroups = new ArrayCollection();
        $this->setBlockDay($blockDay);
        $this->setBlockTime($blockTime);
        $this->device = $device;
        $this->deviceGroup = $deviceGroup;
    }

    /**
     * @return DateTime
     */
    public function getBlockTime()
    {
        return $this->blockTime;
//        return $this->blockTime ? $this->blockTime->format('H') : null;
    }

    /**
     * @param DateTime|integer $blockTime
     * @return DeviceMetricEntity
     */
    public function setBlockTime($blockTime): DeviceMetricEntity
    {
        if (is_numeric($blockTime)) {
            $blockTime = DateTime::createFromFormat('H', $blockTime);
        }

        $blockTime->setDate(1970, 1, 1);

        if ($this->blockTime != $blockTime) $this->blockTime = $blockTime;
        return $this;
    }

    public function getBlockHour()
    {
        return intval($this->blockTime->format('G'));
    }

    /**
     * @return int
     */
    public function getBlockDay(): int
    {
        return $this->blockDay;
    }

    /**
     * @param int $blockDay
     * @return DeviceMetricEntity
     */
    public function setBlockDay(int $blockDay): DeviceMetricEntity
    {
        $this->blockDay = $blockDay;
        return $this;
    }


    /**
     * @param TargetGroupEntity[]|ArrayCollection $targetGroups
     * @return DeviceMetricEntity
     */
    public function setTargetGroups($targetGroups)
    {
        if (!is_array($targetGroups)) $targetGroups = [$targetGroups];
        $this->targetGroups = $targetGroups;

        return $this;
    }


    /**
     * @return TargetGroupEntity[]|ArrayCollection
     */
    public function getTargetGroups()
    {
        return $this->targetGroups;
    }


    /**
     * @return bool
     */
    public function hasDevice(): bool
    {
        return $this->device == true;
    }

    /**
     * @return bool
     */
    public function hasDeviceGroup(): bool
    {
        return $this->deviceGroup == true;
    }


    /**
     * @return DeviceEntity
     */
    public function getDevice(): DeviceEntity
    {
        return $this->device;
    }

    /**
     * @return DeviceGroupEntity
     */
    public function getDeviceGroup(): DeviceGroupEntity
    {
        return $this->deviceGroup;
    }







}