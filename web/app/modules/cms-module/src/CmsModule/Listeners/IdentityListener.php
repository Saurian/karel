<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    IdentityListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Listeners;


use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\IFilter;
use CmsModule\Repositories\UserRepository;
use Kdyby\Events\Subscriber;
use Nette\Security\User;

class IdentityListener implements Subscriber
{

    /** @var UserRepository */
    private $userRepository;

    /** @var CampaignRepository */
    private $campaignRepository;

    /** @var DeviceRepository */
    private $deviceRepository;

    /**
     * IdentityListener constructor.
     *
     * @param UserRepository     $userRepository
     * @param CampaignRepository $campaignRepository
     * @param DeviceRepository   $deviceRepository
     */
    public function __construct(UserRepository $userRepository, CampaignRepository $campaignRepository, DeviceRepository $deviceRepository)
    {
        $this->userRepository     = $userRepository;
        $this->campaignRepository = $campaignRepository;
        $this->deviceRepository   = $deviceRepository;
    }


    /**
     * After logout clear all repository filters
     *
     * @param User $user
     */
    public function onLoggedOut(User $user)
    {
        /** @var IFilter[] $filters */
        $filters = [
            $this->userRepository, $this->campaignRepository, $this->deviceRepository,
        ];

        foreach ($filters as $filter) {
            $filter->clearFilter();
        }
    }


    function getSubscribedEvents()
    {
        return [
            'Nette\Security\User::onLoggedOut'
        ];
    }
}