<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignsFilterControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\Queries\DeviceGroupQuery;
use CmsModule\Repositories\Queries\DeviceQuery;
use Flame\Application\UI\Control;
use Kdyby\Events\Event;
use Nette\Security\User;
use Nette\Utils\Callback;

interface ICampaignsFilterControlFactory
{
    /** @return CampaignsFilterControl */
    function create();
}

/**
 * Class CampaignsFilterControl
 *
 * @package CmsModule\Controls
 * @method onFilter(array $filters)
 * @method onDeviceFiltered($id)
 * @method onDeviceGroupFiltered($id)
 */
class CampaignsFilterControl extends Control
{

    /** @var User @inject */
    public $user;

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    /** @var DeviceEntity[] */
    private $deviceRows = [];

    /** @var DeviceGroupEntity[] */
    private $deviceGroupRows = [];

    /** @var Callback[] events */
    public $onFilter = [];

    /** @var Event[] events */
    public $onDeviceFiltered = [];

    /** @var Event[] events */
    public $onDeviceGroupFiltered = [];


    protected function attached($presenter)
    {
        parent::attached($presenter);

        $this->onDeviceFiltered[] = function ($filter) {
            $this->handleToggleFilterDevice($filter);
            $this->onFilter(['device' => [$filter]]);
        };

        $this->onDeviceGroupFiltered[] = function ($filter) {
            $this->handleToggleFilterDeviceGroup($filter);
            $this->onFilter(['deviceGroup' => [$filter]]);
        };
    }


    public function render()
    {
        $template = $this->getTemplate();

        $template->deviceRows = $this->getDeviceRows();
        $template->deviceGroupRows = $this->getDeviceGroupRows();
        $template->filterDevice = $this->deviceRepository->existFilterDevice() ? $this->deviceRepository->getFilterDevice() : [];
        $template->filterDeviceGroup = $this->deviceGroupRepository->existFilterDeviceGroup() ? $this->deviceGroupRepository->getFilterDeviceGroup() : [];

        $template->render();
    }


    public function handleToggleFilterDeviceGroup($filter)
    {
        if (!$this->deviceGroupRepository->existFilterDeviceGroup()) {
            $filterDeviceGroup[] = intval($filter);

        } else {
            $filterDeviceGroup = $this->deviceGroupRepository->getFilterDeviceGroup();

            if (in_array($filter, $filterDeviceGroup)) {
                $index = array_search($filter, $filterDeviceGroup);
                unset($filterDeviceGroup[$index]);
                $filterDeviceGroup = array_values($filterDeviceGroup);

            } else {
                $filterDeviceGroup[] = intval($filter);
            }
        }

        $this->deviceGroupRepository->setFilterDeviceGroup($filterDeviceGroup);

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesGroupFilter');

        } else {
            $this->redirect('this');
        }
    }


    public function handleToggleFilterDevice($filter)
    {
        if (!$this->deviceRepository->existFilterDevice()) {
            $filterDevice[] = intval($filter);

        } else {
            $filterDevice = $this->deviceRepository->getFilterDevice();

            if (in_array($filter, $filterDevice)) {
                $index = array_search($filter, $filterDevice);
                unset($filterDevice[$index]);
                $filterDevice = array_values($filterDevice);

            } else {
                $filterDevice[] = intval($filter);
            }
        }

        $this->deviceRepository->setFilterDevice($filterDevice);

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesFilter');

        } else {
            $this->redirect('this');
        }
    }


    public function handleClearDeviceFilter()
    {
        if ($this->deviceRepository->existFilterDevice()) {
            $this->deviceRepository->clearFilterDevices();
        }


        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesFilter');

        } else {
            $this->redirect('this');
        }
    }


    public function handleClearDeviceGroupFilter()
    {
        if ($this->deviceGroupRepository->existFilterDeviceGroup()) {
            $this->deviceGroupRepository->clearFilterDeviceGroup();
        }

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesGroupFilter');

        } else {
            $this->redirect('this');
        }
    }


    public function handleFilter()
    {
        $filterDevice = $this->deviceRepository->existFilterDevice() ? $this->deviceRepository->getFilterDevice() : [];
        $filterDeviceGroup = $this->deviceGroupRepository->existFilterDeviceGroup() ? $this->deviceGroupRepository->getFilterDeviceGroup() : [];

        $this->onFilter(['device' => $filterDevice, 'deviceGroup' => $filterDeviceGroup]);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();

        } else {
            $this->redirect('this');
        }
    }



    /**
     * @return DeviceEntity[]
     */
    public function getDeviceRows()
    {
        if (!$this->deviceRows) {
            $query = (new DeviceQuery());

            if (!$this->user->isAllowed('Cms:Device', 'listAllDevices')) {
                $query->byUser($this->user);
            }

            if ($this->deviceRepository->existFilterDevice()) {
//                $query->inDevices($this->deviceRepository->getFilterDevice());
            }

            $this->setDeviceRows($this->deviceRepository->fetch($query));
        }

        return $this->deviceRows;
    }


    /**
     * @return DeviceGroupEntity[]
     */
    public function getDeviceGroupRows()
    {
        if (!$this->deviceGroupRows) {
            $query = (new DeviceGroupQuery());

            if (!$this->user->isAllowed('Cms:Device', 'listAllDevices')) {
                $query->byUser($this->user);
            }

            if ($this->deviceRepository->existFilterDevice()) {
//                $query->inDevices($this->deviceRepository->getFilterDevice());
            }

            $this->setDeviceGroupRows($this->deviceGroupRepository->fetch($query));
        }

        return $this->deviceGroupRows;
    }


    /**
     * @param DeviceEntity[] $rows
     *
     * @return $this
     */
    public function setDeviceRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->getId()] = $row;
        }

        $this->deviceRows = $_rows;
        return $this;
    }

    /**
     * @param DeviceGroupEntity[] $rows
     *
     * @return $this
     */
    public function setDeviceGroupRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->getId()] = $row;
        }

        $this->deviceGroupRows = $_rows;
        return $this;
    }




}