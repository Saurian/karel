<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UserEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Devrun\Doctrine\Entities\PositionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class UserEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\UserRepository")
 * @ORM\Table(name="users",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="username_mail_idx", columns={"username", "mail"}),
 *      @ORM\UniqueConstraint(name="username_idx", columns={"username"})
 * },
 *  indexes={
 *      @ORM\Index(name="first_last_name_idx", columns={"first_name", "last_name"}),
 *      @ORM\Index(name="position_idx", columns={"position"}),
 *      @ORM\Index(name="user_active_idx", columns={"active"}),
 *      @ORM\Index(name="role_idx", columns={"role"}),
 *      @ORM\Index(name="mail_idx", columns={"mail"}),
 *      @ORM\Index(name="password_idx", columns={"password"}),
 * })
 *
 * @package CmsModule\Entities
 */
class UserEntity implements IDeviceEntity, IRoleEntity
{
    use Identifier;
    use DateTimeTrait;
    use PositionTrait;
    use BlameableTrait;
    use MagicAccessors;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $mail;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $newPassword;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    /**
     * @var string enum
     * @ORM\Column(type="string", length=32)
     */
    protected $role;


    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DeviceEntity", inversedBy="devicesUsers")
     * @ORM\JoinTable(name="users_devices")
     */
    protected $devices;

    /**
     * @var DeviceGroupEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DeviceGroupEntity", inversedBy="devicesGroupsUsers")
     * @ORM\JoinTable(name="users_devices_groups")
     */
    protected $devicesGroups;

    /**
     * @var TemplateEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="TemplateEntity", mappedBy="users")
     */
    protected $templates;


    /**
     * UserEntity constructor.
     */
    public function __construct($firstName, $lastName, $mail, $username, $role)
    {
        $this->firstName     = $firstName;
        $this->lastName      = $lastName;
        $this->mail          = $mail;
        $this->username      = $username;
        $this->role          = $role;
        $this->devices       = new ArrayCollection();
        $this->devicesGroups = new ArrayCollection();
    }


    /**
     * @param string $mail
     *
     * @return $this
     */
    public function setMail($mail)
    {
        $this->mail     = $mail;
        $this->username = $mail;
        return $this;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }


    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = md5($this->username . $password);
        return $this;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     *
     * @return $this
     */
    public function setNewPassword(string $newPassword)
    {
        $this->newPassword = md5($newPassword);
        return $this;
    }


    /**
     * @return $this
     */
    public function resetNewPassword()
    {
        $this->newPassword = null;
        return $this;
    }


    public function setPasswordFromNewPassword()
    {
        if ($this->newPassword) {
            $this->password    = $this->newPassword;
            $this->newPassword = null;
        }
    }

    /**
     * @return DeviceEntity[]|ArrayCollection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
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
     * @return DeviceGroupEntity[]|ArrayCollection
     */
    public function getDevicesGroups()
    {
        return $this->devicesGroups;
    }

    /**
     * @param DeviceGroupEntity $deviceGroupEntity
     *
     * @return $this
     */
    public function addDeviceGroup(DeviceGroupEntity $deviceGroupEntity)
    {
        if (!$this->devicesGroups->contains($deviceGroupEntity)) {
            $this->devicesGroups->add($deviceGroupEntity);
        }

        return $this;
    }

    /**
     * @param DeviceGroupEntity $deviceGroupEntity
     *
     * @return $this
     */
    public function removeDeviceGroup(DeviceGroupEntity $deviceGroupEntity)
    {
        if ($this->devicesGroups->contains($deviceGroupEntity)) {
            $this->devicesGroups->removeElement($deviceGroupEntity);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
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
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function setRole(string $role)
    {
        $this->role = $role;
        return $this;
    }

    public function getName()
    {
        return $this->getFullName();
    }

    public function getFullName()
    {
        return "{$this->firstName} {$this->lastName}";
    }

    function __toString()
    {
        return $this->getFullName();
    }


}