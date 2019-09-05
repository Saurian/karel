<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2018
 *
 * @file    IDeviceEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Doctrine\Common\Collections\ArrayCollection;

interface IDeviceEntity
{

    /**
     * @return DeviceEntity[]|ArrayCollection
     */
    public function getDevices();

    /**
     * @param DeviceEntity $deviceEntity
     *
     * @return $this
     */
    public function addDevice(DeviceEntity $deviceEntity);

    /**
     * @param DeviceEntity $deviceEntity
     *
     * @return $this
     */
    public function removeDevice(DeviceEntity $deviceEntity);

    /**
     * @return DeviceGroupEntity[]|ArrayCollection
     */
    public function getDevicesGroups();

    /**
     * @param DeviceGroupEntity $deviceGroupEntity
     *
     * @return $this
     */
    public function addDeviceGroup(DeviceGroupEntity $deviceGroupEntity);

    /**
     * @param DeviceGroupEntity $deviceGroupEntity
     *
     * @return $this
     */
    public function removeDeviceGroup(DeviceGroupEntity $deviceGroupEntity);


}