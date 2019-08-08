<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    DeviceLogFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;

use CmsModule\Entities\DeviceLogEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Repositories\DeviceLogRepository;
use Kdyby\Doctrine\EntityManager;

class DeviceLogFacade
{

    /** @var DeviceLogRepository */
    private $deviceApiLogRepository;

    /** @var EntityManager */
    private $em;


    /**
     * DeviceLogFacade constructor.
     *
     * @param DeviceLogRepository $deviceApiLogRepository
     */
    public function __construct(DeviceLogRepository $deviceApiLogRepository)
    {
        $this->em = $deviceApiLogRepository->getEntityManager();
        $this->deviceApiLogRepository = $deviceApiLogRepository;

    }


    public function createLog($ssid, $command, DeviceEntity $deviceEntity, $params, $result)
    {
        $entity = new DeviceLogEntity($ssid, $command, $deviceEntity);

        $valid = (isset($result['result']) && is_bool($result['result']))
            ? $result['result']
            : true;

        $reason = isset($result['reason'])
            ? $result['reason']
            : null;

        $entity->setValid($valid);

        if ($params) {
            $entity->setParams($params);
        }

        if ($result) {
            $entity->setResult($result);
        }

        if ($reason) {
            $entity->setReason($reason);
        }

        $this->em->persist($entity)->flush();

    }


}