<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    StatisticPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;


use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\TemplateRepository;
use CmsModule\Repositories\UserRepository;

class StatisticPresenter extends BasePresenter
{
    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var DeviceRepository @inject */
    public $deviceRepository;



    public function renderDefault()
    {
        $userQuery     = $this->userRepository->getUserAllowedQuery($this->user);
        $deviceQuery   = $this->deviceRepository->getUserAllowedQuery($this->user);
        $campaignQuery = $this->campaignRepository->getUserAllowedQuery($this->user);
        $templateQuery = $this->templateRepository->getUserAllowedQuery($this->user);

        $this->template->users     = $this->userRepository->fetch($userQuery)->count();
        $this->template->devices   = $this->deviceRepository->fetch($deviceQuery)->count();
        $this->template->campaigns = $this->campaignRepository->fetch($campaignQuery)->count();
        $this->template->templates = $this->templateRepository->fetch($templateQuery)->count();


    }


}