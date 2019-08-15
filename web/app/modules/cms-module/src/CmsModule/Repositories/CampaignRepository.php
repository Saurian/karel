<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Repositories\Queries\CampaignQuery;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

class CampaignRepository extends EntityRepository implements IFilter
{

    const SESSION_NAME = 'campaignFilter';

    use FilterRepositoryTrait;
    use PostProcessingTrait;


    public function getActiveRowsCount()
    {
        $query = (new CampaignQuery())->isActive();
        return $this->fetch($query)->getTotalCount();
    }


    public function getNonActiveRowsCount()
    {
        $query = (new CampaignQuery())->isNotActive();
        return $this->fetch($query)->getTotalCount();
    }

    public function getAllRowsCount()
    {
        $query = (new CampaignQuery());
        return $this->fetch($query)->getTotalCount();
    }


    public function getOpenDetailCampaign()
    {
        $section = $this->getSection();
        return isset($section->openDetailCampaign) ? $section->openDetailCampaign : null;
    }

    public function setOpenDetailCampaign($id)
    {
        $section = $this->getSection();
        return $section->openDetailCampaign = $id;
    }




    public function getQuery($baseAlias = null)
    {
        $query = (new CampaignQuery($baseAlias));

        return $query;
    }


    public function getUserAllowedQuery(User $user, $filterDevice = null, $filterGroupDevice = null, $campaign = null)
    {
        $query = (new CampaignQuery('entity'));

        if ($filterDevice && !empty($filterDevice)) {
            $query->byDevices($filterDevice);
        }
        if ($filterGroupDevice && !empty($filterGroupDevice)) {
            $query->orDevicesGroups($filterGroupDevice);
        }
        if (!$user->isAllowed('Cms:Campaign', 'listAllCampaigns')) {
            $query->byUser($user);
        }
        if ($campaign) {
            $query->byCampaigns($campaign);
        }

        return $query;
    }



}