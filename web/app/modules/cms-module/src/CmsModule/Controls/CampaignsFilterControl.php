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
use CmsModule\Presenters\CampaignPresenter;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use Devrun\CmsModule\Controls\DataGrid;
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
 * @method onRedraw()
 */
class CampaignsFilterControl extends Control
{

    /** @var User @inject */
    public $user;

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    /** @var Callback[] events */
    public $onFilter = [];

    /** @var Event[] events */
    public $onRedraw = [];

    /** @var DeviceEntity[] */
    private $deviceRows = [];

    /** @var DeviceGroupEntity[] */
    private $deviceGroupRows = [];

    /** @var DataGrid */
    private $campaignGridControl;


    /** @var array */
    private $filter = [];


    public function render()
    {
        $template = $this->getTemplate();

        $template->deviceRows = $this->getDeviceRows();
        $template->deviceGroupRows = $this->getDeviceGroupRows();
        $template->filterDevice = $this->getDeviceFilterData();
        $template->filterDeviceGroup = $this->getDeviceGroupFilterData();

        $template->render();
    }

    /**
     * @return DeviceEntity[]
     */
    public function getDeviceRows()
    {
        if (!$this->deviceRows) {
            $result = $this->deviceRepository->getCachedResult($this->deviceRepository->getUserAllowedQueryBuilder($this->user));
            $this->deviceRows = $this->deviceRepository->getAssoc($result);
        }

        return $this->deviceRows;
    }


    /**
     * @return DeviceGroupEntity[]
     */
    public function getDeviceGroupRows()
    {
        if (!$this->deviceGroupRows) {
            $result = $this->deviceGroupRepository->getCachedResult($this->deviceGroupRepository->getUserAllowedQueryBuilder($this->user));
            $this->deviceGroupRows = $this->deviceGroupRepository->getAssoc($result);
        }

        return $this->deviceGroupRows;
    }


    /**
     * @return array
     */
    private function getDeviceFilterData()
    {
        return (array) $this->campaignGridControl->getSessionData('devices');
    }

    /**
     * @return array
     */
    private function getDeviceGroupFilterData()
    {
        return (array) $this->campaignGridControl->getSessionData('deviceGroups');
    }



    public function handleToggleFilterDeviceGroup($filter)
    {
        $filterDevices = $this->getDeviceFilterData();
        $filterDeviceGroup = $this->getDeviceGroupFilterData();

        if (in_array($filter, $filterDeviceGroup)) {
            $index = array_search($filter, $filterDeviceGroup);
            unset($filterDeviceGroup[$index]);
            $filterDeviceGroup = array_values($filterDeviceGroup);

        } else {
            $filterDeviceGroup[] = intval($filter);
        }

        $this->campaignGridControl->saveSessionData('deviceGroups', $filterDeviceGroup);

        $this->filter['devices'] = $filterDevices;
        $this->filter['deviceGroups'] = $filterDeviceGroup;

        $this->onFilter($this->filter);

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesGroupFilter');

        } else {
            $this->redirect('this');
        }
    }

    public function handleToggleFilterDevice($filter)
    {
        $filterDevice = $this->getDeviceFilterData();
        $filterDeviceGroup = $this->getDeviceGroupFilterData();

        if (in_array($filter, $filterDevice)) {
            $index = array_search($filter, $filterDevice);
            unset($filterDevice[$index]);
            $filterDevice = array_values($filterDevice);

        } else {
            $filterDevice[] = intval($filter);
        }

        $this->campaignGridControl->saveSessionData('devices', $filterDevice);

        $this->filter['devices'] = $filterDevice;
        $this->filter['deviceGroups'] = $filterDeviceGroup;

        $this->onFilter($this->filter);

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesFilter');

        } else {
            $this->redirect('this');
        }
    }

    public function handleClearDeviceFilter()
    {
        $filterDeviceGroup = $this->getDeviceGroupFilterData();
        $this->campaignGridControl->saveSessionData('devices', []);

        $this->filter['devices'] = [];
        $this->filter['deviceGroups'] = $filterDeviceGroup;

        $this->onFilter($this->filter);

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesFilter');

        } else {
            $this->redirect('this');
        }
    }

    public function handleClearDeviceGroupFilter()
    {
        $filterDevice = $this->getDeviceFilterData();
        $this->campaignGridControl->saveSessionData('deviceGroups', []);
//
        $this->filter['devices'] = $filterDevice;
        $this->filter['deviceGroups'] = [];
//
        $this->onFilter($this->filter);

        if ($this->presenter->isAjax()) {
            $this->redrawControl('devicesGroupFilter');

        } else {
            $this->redirect('this');
        }
    }

    public function handleFilter()
    {
        $filterDevice = $this->getDeviceFilterData();
        $filterDeviceGroup = $this->getDeviceGroupFilterData();

        $this->filter['devices'] = $filterDevice;
        $this->filter['deviceGroups'] = $filterDeviceGroup;

        $this->onFilter($this->filter);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();

        } else {
            $this->redirect('this');
        }
    }

    protected function attached($presenter)
    {
        parent::attached($presenter);

        if ($presenter instanceof CampaignPresenter) {
            if (isset($presenter['campaignGridControl'])) {
                $this->campaignGridControl = $presenter['campaignGridControl'];

                $this->filter = $this->campaignGridControl->getFiltersSet();
            }
        }

        $this->onRedraw[] = function () {
            $this->redrawControl();
        };

    }


}