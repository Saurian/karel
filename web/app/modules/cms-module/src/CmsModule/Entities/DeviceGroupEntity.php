<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    GroupDevicesEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\NestedEntityTrait;
use Devrun\Doctrine\Entities\PositionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class DeviceGroupEntity
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\DeviceGroupRepository")
 * @ORM\Table(name="device_group", indexes={
 *     @ORM\Index(name="active_idx", columns={"active"}),
 *     @ORM\Index(name="position_idx", columns={"position"}),
 *     @ORM\Index(name="name_idx", columns={"name"}),
 * })
 *
 * @package CmsModule\Entities
 */
class DeviceGroupEntity
{
    use Identifier;
    use DateTimeTrait;
    use BlameableTrait;
    use MagicAccessors;
    use PositionTrait;
    use NestedEntityTrait;


    /**
     * var DeviceGroupEntity
     *
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="DeviceGroupEntity")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $root;

    /**
     * @var DeviceGroupEntity
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="DeviceGroupEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var DeviceGroupEntity[]|ArrayCollection
     * _@_ORM\Cache("NONSTRICT_READ_WRITE")
     * @ORM\OneToMany(targetEntity="DeviceGroupEntity", mappedBy="parent", cascade={"persist"})
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;


    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeviceEntity", mappedBy="deviceGroup")
     */
    protected $devicesOld;

    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DeviceEntity", inversedBy="devicesGroups")
     * @ORM\JoinTable(name="devices_groups")
     */
    protected $devices;


    /**
     * @var CampaignEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="CampaignEntity", mappedBy="devicesGroups")
     */
    protected $campaigns;

    /**
     * @var UserEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="UserEntity", mappedBy="devicesGroups")
     */
    protected $devicesGroupsUsers;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    protected $active = false;


    /**
     * DeviceGroupEntity constructor.
     *
     * @param string $name
     */
    public function __construct(string $name, string $category = '')
    {
        $this->name               = $name;
        $this->category           = $category;
        $this->devices            = new ArrayCollection();
        $this->devicesOld            = new ArrayCollection();
        $this->campaigns          = new ArrayCollection();
        $this->devicesGroupsUsers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        if (is_string($active)) $active = json_decode($active);
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * @return mixed
     */
    public function getRoot()
    {

        die("ASDD");

        return $this->root;
    }

    /**
     * @return DeviceGroupEntity
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * @param DeviceGroupEntity|null $parent
     *
     * @return $this
     */
    public function setParent(DeviceGroupEntity $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }


    /**
     * add device to group
     *
     * @param DeviceEntity $deviceEntity
     *
     * @return $this
     */
    public function addDevice(DeviceEntity $deviceEntity)
    {
        if (!$this->devices->contains($deviceEntity)) {
            $this->devices->add($deviceEntity);
        }

        return $this;
    }


    /**
     * remove device from group
     *
     * @param DeviceEntity $deviceEntity
     *
     * @return $this
     */
    public function removeDevice(DeviceEntity $deviceEntity)
    {
        if ($this->devices->contains($deviceEntity)) {
            $this->devices->removeElement($deviceEntity);
        }

        return $this;
    }

    /**
     * @param DeviceEntity $deviceEntity
     *
     * @return bool
     */
    public function hasDevice(DeviceEntity $deviceEntity)
    {
        return $this->devices->contains($deviceEntity);
    }


    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasDeviceById($id)
    {
        foreach ($this->devices as $device) {
            if ($device->getId() == $id) return true;
        }

        return false;
    }





}