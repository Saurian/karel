<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    DeviceLogEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class DeviceLogEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\DeviceLogRepository")
 * @ORM\Table(name="device_log", indexes={
 *     @ORM\Index(name="ssid_idx", columns={"ssid"}),
 *     @ORM\Index(name="command_idx", columns={"command"}),
 *     @ORM\Index(name="reason_idx", columns={"reason"}),
 *     @ORM\Index(name="valid_idx", columns={"valid"}),
 * })
 *
 * @package CmsModule\Entities
 */
class DeviceLogEntity
{
    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $ssid;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $command;

    /**
     * @var DeviceEntity
     * @ORM\ManyToOne(targetEntity="DeviceEntity", inversedBy="logs")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $device;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $valid;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $params;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $result;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $reason;


    /**
     * DeviceLogEntity constructor.
     *
     * @param string       $ssid
     * @param string       $command
     * @param DeviceEntity $device
     */
    public function __construct(string $ssid, string $command, DeviceEntity $device)
    {
        $this->ssid    = $ssid;
        $this->command = $command;
        $this->device  = $device;
    }

    /**
     * @param string $ssid
     *
     * @return DeviceLogEntity
     */
    public function setSsid(string $ssid): DeviceLogEntity
    {
        $this->ssid = $ssid;
        return $this;
    }

    /**
     * @param string $command
     *
     * @return DeviceLogEntity
     */
    public function setCommand(string $command): DeviceLogEntity
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @param DeviceEntity $device
     *
     * @return DeviceLogEntity
     */
    public function setDevice(DeviceEntity $device): DeviceLogEntity
    {
        $this->device = $device;
        return $this;
    }

    /**
     * @param array $params
     *
     * @return DeviceLogEntity
     */
    public function setParams(array $params): DeviceLogEntity
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param array $result
     *
     * @return DeviceLogEntity
     */
    public function setResult(array $result): DeviceLogEntity
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param string $reason
     *
     * @return DeviceLogEntity
     */
    public function setReason(string $reason): DeviceLogEntity
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @param bool $valid
     *
     * @return DeviceLogEntity
     */
    public function setValid(bool $valid): DeviceLogEntity
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }




}