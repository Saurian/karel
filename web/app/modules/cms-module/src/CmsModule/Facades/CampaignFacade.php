<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Repositories\CampaignRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\Session;
use Nette\SmartObject;

/**
 * Class CampaignFacade
 *
 * @package CmsModule\Facades
 * @method onActivate(CampaignEntity $campaignEntity)
 */
class CampaignFacade
{
    use SmartObject;

    use PositionableTrait;

    use DeviceFacadeTrait;

    /** @var EntityManager */
    private $em;

    /** @var CampaignRepository */
    private $campaignRepository;

    /** @var MediaDataFacade */
    private $mediaDataFacade;

    /** @var Session */
    private $session;

    /** @var Callback[] */
    public $onActivate = [];



    /**
     * CampaignFacade constructor.
     *
     * @param CampaignRepository $campaignRepository
     */
    public function __construct(CampaignRepository $campaignRepository, MediaDataFacade $mediaDataFacade, Session $session)
    {
        $this->campaignRepository = $campaignRepository;
        $this->mediaDataFacade    = $mediaDataFacade;
        $this->session            = $session;
        $this->em                 = $campaignRepository->getEntityManager();
    }


    public function setActive(CampaignEntity $campaignEntity, $active)
    {
        $campaignEntity->setActive($active);
        $this->em->persist($campaignEntity);

        $this->onActivate($campaignEntity);
        $this->em->flush();
    }


    public function removeMediaFromCampaign(CampaignEntity $campaignEntity)
    {
        /** @var MediumDataEntity[] $mediaEntities */
        if ($mediaEntities = $this->em->getRepository(MediumDataEntity::class)->findBy(['campaign' => $campaignEntity])) {
            foreach ($mediaEntities as $mediaEntity) {
                $this->mediaDataFacade->removeFileFromMedium($mediaEntity);
            }
        }
    }


    public function getRepository()
    {
        return $this->campaignRepository;
    }


    /**
     * @return CampaignRepository
     */
    public function getCampaignRepository()
    {
        return $this->campaignRepository;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }


    private function getSection()
    {
        return $this->session->getSection('campaign');
    }


    public function setNewCampaignSelectDevice($device)
    {
        $section = $this->getSection();
        $section->device = $device;
    }

    /**
     * @return integer
     */
    public function getNewCampaignSelectDevice()
    {
        $section = $this->getSection();
        return $section->device;
    }

    /**
     * @return bool
     */
    public function isNewCampaignSelectDevice()
    {
        $section = $this->getSection();
        return isset($section->device);
    }

    public function cleanNewCampaignSelectDevice()
    {
        $section = $this->getSection();
        unset($section->device);
    }




}