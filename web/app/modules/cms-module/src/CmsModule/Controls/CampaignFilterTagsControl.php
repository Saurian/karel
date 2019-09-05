<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    CampaignFilterTagsControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use Flame\Application\UI\Control;

interface ICampaignFilterTagsControlFactory
{
    /** @return CampaignFilterTagsControl */
    function create();
}

/**
 * Class CampaignFilterTagsControl
 *
 * @package CmsModule\Controls
 * @method onDeviceFilter($id)
 * @method onDeviceGroupFilter($id)
 */
class CampaignFilterTagsControl extends Control
{

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    public $onFiltered = [];

    /** @var Callback[] events */
    public $onDeviceFilter = [];

    /** @var Callback[] events */
    public $onDeviceGroupFilter = [];


    protected function attached($presenter)
    {
        parent::attached($presenter);
        $this->onFiltered[] = [$this, 'onFiltered'];
    }


    public function handleRemoveFilterDevice($id)
    {
        $this->onDeviceFilter($id);
        $this->redrawThis();
    }

    public function handleRemoveFilterDeviceGroup($id)
    {
        $this->onDeviceGroupFilter($id);
        $this->redrawThis();
    }

    public function render()
    {
        $template = $this->getTemplate();

        $template->filterDeviceNames      = $this->getFilterDeviceNames();
        $template->filterDeviceGroupNames = $this->getFilterDeviceGroupNames();

        $template->render();
    }


    private function getFilterDeviceNames()
    {
        $deviceNames  = [];
        $filterDevice = $this->deviceRepository->existFilterDevice() ? $this->deviceRepository->getFilterDevice() : [];

        if ($filterDevice) {
            $deviceNames = $this->deviceRepository->findPairs(['id =' => $filterDevice], 'name');
        }

        return $deviceNames;
    }


    private function getFilterDeviceGroupNames()
    {
        $deviceGroupNames  = [];
        $filterDeviceGroup = $this->deviceGroupRepository->existFilterDeviceGroup() ? $this->deviceGroupRepository->getFilterDeviceGroup() : [];

        if ($filterDeviceGroup) {
            $deviceGroupNames = $this->deviceGroupRepository->findPairs(['id =' => $filterDeviceGroup], 'name');
        }

        return $deviceGroupNames;
    }


    public function onFiltered($filter)
    {
        $this->redrawThis();
    }


    private function redrawThis()
    {
        if ($this->presenter->isAjax()) {
            $this->redrawControl('tags');

        } else {
            $this->presenter->redirect('this');
        }
    }


}