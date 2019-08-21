<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\PositionTrait;
use Devrun\Doctrine\Entities\VersionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\Strings;

/**
 * Class DeviceEntity
 * @ORM\Cache(region="device", usage="NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\DeviceRepository")
 * @ORM\Table(name="device",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="sn_rotate_idx", columns={"sn", "sn_rotate"}),
 *  },
 *  indexes={
 *     @ORM\Index(name="tag_idx", columns={"tag"}),
 *     @ORM\Index(name="device_active_idx", columns={"active"}),
 *     @ORM\Index(name="device_name_idx", columns={"name"}),
 *     @ORM\Index(name="position_idx", columns={"position"}),
 *     @ORM\Index(name="keywords_idx", columns={"keywords"}, flags={"fulltext"}),
 * })
 * @package CmsModule\Entities
 */
class DeviceEntity
{
    use Identifier;
    use DateTimeTrait;
    use BlameableTrait;
    use PositionTrait;
    use VersionTrait;
    use MagicAccessors;
    use TagsTrait;


    /**
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    protected $sn;

    /**
     * @var string
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $snRotate;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $psw;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $street = '';

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $strno;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $zip = '';

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $city = '';

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    protected $active = false;

    /**
     * @var string
     * @ORM\Column(type="text", length=65536, nullable=true, options={"comment":"fullSearch keyword"})
     */
    protected $keywords;


    /**
     * @var CampaignEntity
     * @ORM\ManyToOne(targetEntity="CampaignEntity", inversedBy="defaultDevices")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $defaultCampaign;

    /**
     * @var CampaignEntity
     * @ORM\ManyToOne(targetEntity="CampaignEntity", inversedBy="loopDevices")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $loopCampaign;



    /**
     * @deprecated
     * @var DeviceGroupEntity
     * @ORM\ManyToOne(targetEntity="DeviceGroupEntity", inversedBy="devicesOld", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $deviceGroup;


    /**
     * @var DeviceGroupEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DeviceGroupEntity", mappedBy="devices")
     */
    protected $devicesGroups;


    /**
     * @var CampaignEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="CampaignEntity", mappedBy="devices")
     */
    protected $campaigns;

    /**
     * @var UserEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="UserEntity", mappedBy="devices")
     */
    protected $devicesUsers;

    /**
     * @var DeviceLogEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeviceLogEntity", mappedBy="device")
     */
    protected $logs;


    /**
     * DeviceEntity constructor.
     */
    public function __construct()
    {
        $this->campaigns = new ArrayCollection();
        $this->devicesGroups = new ArrayCollection();
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSn()
    {
        return $this->sn;
    }

    /**
     * @param string $sn
     *
     * @return $this
     */
    public function setSn(string $sn)
    {
        $this->sn = $sn;
        return $this;
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
     * @deprecated
     *
     * @return DeviceGroupEntity
     */
    public function getDeviceGroup()
    {
        return $this->deviceGroup;
    }

    /**
     * @deprecated
     * @param DeviceGroupEntity $deviceGroup
     */
    public function setDeviceGroup($deviceGroup = null)
    {
        $this->deviceGroup = $deviceGroup;
    }

    /**
     * @return DeviceGroupEntity[]|ArrayCollection
     */
    public function getDevicesGroups()
    {
        return $this->devicesGroups;
    }

    /**
     * @param DeviceGroupEntity[]|ArrayCollection $devicesGroups
     * @return DeviceEntity
     */
    public function setDevicesGroups($devicesGroups)
    {
        $this->devicesGroups = $devicesGroups;
        return $this;
    }





    /**
     * @return CampaignEntity[]|ArrayCollection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * @return string
     */
    public function getPsw()
    {
        return $this->psw;
    }


    /**
     * @param $password
     *
     * @return string
     */
    public function getHashDevicePassword($password)
    {
        $last3chars = Strings::substring($this->sn, Strings::length($this->sn) - 3);

        $pass2crypt = md5($password . $last3chars);
        $final_pass = md5($pass2crypt . $last3chars);
        return $final_pass;
    }


    /**
     * @param string $psw
     *
     * @return $this
     */
    public function setPsw($psw)
    {
        if ($psw) {
            $last3chars = Strings::substring($this->sn, Strings::length($this->sn) - 3);

            $pass2crypt = md5($psw . $last3chars);
            $final_pass = md5($pass2crypt . $last3chars);

            $this->psw = $final_pass;
        }

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

    function __toString()
    {
        return $this->name;
    }


}