<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2018
 *
 * @file    TDeviceFacadeTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\IDeviceEntity;

trait DeviceFacadeTrait
{

    /**
     * update devices
     *
     * @param IDeviceEntity  $entity
     * @param DeviceEntity[] $devices
     * @param array          $selectDevices
     */
    public function updateDevices(IDeviceEntity $entity, $devices, array $selectDevices)
    {
        $entityDevices = $this->entityPairsRows($entity->getDevices());
        $selectDevicesIsInEntity = array_diff(array_keys($entityDevices), $selectDevices);
        $removeDevices = array_intersect($selectDevicesIsInEntity, array_keys($devices));

        foreach ($removeDevices as $removeDevice) {
            $entity->removeDevice($devices[$removeDevice]);
        }

        $selectDevicesIsInEntity = array_diff($selectDevices, array_keys($entityDevices));
        $addDevices = array_intersect(array_keys($devices), $selectDevicesIsInEntity);

        foreach ($addDevices as $addDevice) {
            $entity->addDevice($devices[$addDevice]);
        }

    }


    /**
     * update device groups
     *
     * @param IDeviceEntity       $entity
     * @param DeviceGroupEntity[] $devicesGroups
     * @param array               $selectDevicesGroups
     */
    public function updateDevicesGroups(IDeviceEntity $entity, $devicesGroups, array $selectDevicesGroups)
    {
        $entityDevicesGroups = $this->entityPairsRows($entity->getDevicesGroups());
        $selectDevicesGroupsIsInEntity = array_diff(array_keys($entityDevicesGroups), $selectDevicesGroups);
        $removeDevicesGroups = array_intersect($selectDevicesGroupsIsInEntity, array_keys($devicesGroups));

        foreach ($removeDevicesGroups as $removeDevicesGroup) {
            $entity->removeDeviceGroup($devicesGroups[$removeDevicesGroup]);
        }

        $selectDevicesGroupsIsInEntity = array_diff($selectDevicesGroups, array_keys($entityDevicesGroups));
        $addDevicesGroups = array_intersect(array_keys($devicesGroups), $selectDevicesGroupsIsInEntity);

        foreach ($addDevicesGroups as $addDevicesGroup) {
            $entity->addDeviceGroup($devicesGroups[$addDevicesGroup]);
        }
    }


    /**
     * associate entity by id
     *
     * @param $rows
     *
     * @return array
     */
    private function entityPairsRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->id] = $row;
        }

        return $_rows;
    }



}