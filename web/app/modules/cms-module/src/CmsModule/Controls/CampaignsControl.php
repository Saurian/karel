<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignsControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Facades\CampaignFacade;
use CmsModule\Facades\MediaDataFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\ICampaignFormFactory;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\CampaignRepository;
use CmsModule\Repositories\Queries\CampaignQuery;
use CmsModule\Repositories\UserRepository;
use Flame\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;
use Tracy\Debugger;

interface ICampaignsControlFactory
{
    /** @return CampaignsControl */
    function create();
}

class CampaignsControl extends Control
{
    /** @var CampaignEntity[] */
    private $rows = [];

    /** @var DeviceEntity[] */
    private $devices = [];

    /** @var DeviceGroupEntity[] */
    private $devicesGroups = [];

    /** @var User @inject */
    public $user;

    /** @var UserRepository @inject */
    public $userRepository;


    /** @var ICampaignFormFactory @inject */
    public $campaignFormFactory;

    /** @var CampaignRepository @inject */
    public $campaignRepository;

    /** @var CampaignFacade @inject */
    public $campaignFacade;

    /** @var MediaDataFacade @inject */
    public $mediaDataFacade;


    public function handleSetFilter($active)
    {
        $this->campaignRepository->setFilterActive($active);
        $filter    = $this->campaignRepository->getFilterActive();

        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        $message = "Nastaven filtr";
        if ($filter === "1") {
            $message = "Nastaven filtr `Správa aktivních kampaní`";

        } elseif ( $filter === "0") {
            $message = "Nastaven filtr `Správa neaktivních kampaní`";

        } elseif ( $filter === null) {
            $message = "Nastaven filtr `Správa všech kampaní`";
        }

        $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa Kampaní', FlashMessageControl::TOAST_INFO);

        $this->redrawControl();
        $presenter->redrawControl('flash');
    }


    public function handleToggleActive($uid, $checked)
    {
        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        /** @var CampaignEntity $element */
        if ($element = $this->campaignRepository->find($uid)) {

            $this->campaignFacade->setActive($element, $checked);

            $message = "Zařízení `{$element->getName()}` je nyní " . ($element->isActive() ? 'aktivní' : 'neaktivní');
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa zařízení', FlashMessageControl::TOAST_SUCCESS);
        }

        if ($presenter->isAjax()) {
            $this->redrawControl('filter');
            $this->redrawControl('items');
//            $this->redrawControl('devicesItems');
            $presenter->redrawControl('flash');

        } else {
            $this->redirect('this');
        }
    }



    public function handleDetail($id)
    {

        if ($this->presenter->isAjax()) {
//            $this->redrawControl('campaigns');
            $this->redrawControl('items');

        } else $this->redirect('this');
    }

    public function handleChangeTemplate($id, $tid)
    {
        /** @var CampaignEntity $campaign */
        if ($campaign = $this->campaignRepository->find($id)) {

            /** @var BasePresenter $presenter */
            $presenter = $this->getPresenter();

            $em = $this->campaignRepository->getEntityManager();
            if ($template = $em->getRepository(TemplateEntity::getClassName())->find($tid)) {

                $campaign->template = $template;

                if ($mediaDataEntities = $em->getRepository(MediumDataEntity::getClassName())->findBy(['campaign' => $campaign])) {
                    $em->remove($mediaDataEntities);

                    $presenter->flashMessage("Smazána data pro šablonu", FlashMessageControl::TOAST_TYPE, 'Správa kampaně', FlashMessageControl::TOAST_WARNING);
                    $presenter->redrawControl('flash');
                }

                $em->persist($campaign)->flush();
            }

        }


        if ($this->presenter->isAjax()) {
//            $this->redrawControl();
            $this->redrawControl('items');
//            $this->redrawControl('campaigns');

        } else $this->redirect('this');
    }


    public function render()
    {
        Debugger::barDump(__FUNCTION__);


        $template = $this->getTemplate();

        $template->rows = $this->getRows();

        $template->debug  = rand(10, 500);
        $template->debug1 = rand(10, 500);




//        dump($this->user);

        $template->allCampaignCount       = $this->campaignRepository->getAllRowsCount();
        $template->activeCampaignCount    = $this->campaignRepository->getActiveRowsCount();
        $template->nonActiveCampaignCount = $this->campaignRepository->getNonActiveRowsCount();

        $template->render();
    }


    protected function createComponentCampaignsControl($name)
    {
        Debugger::barDump(__FUNCTION__);

        $self = $this;

        return new Multiplier(function ($id) use ($self, $name) {
            Debugger::barDump("Multiplier $id");

//            Debugger::barDump($self->rows);

            $entity = $self->getRows()[$id];

            $entity->synchronizeMediaDataFromTemplate();

            $mediumData = $entity->getMediaData();


//            Debugger::barDump($entity->getTemplate());

            $form = $this->campaignFormFactory->create();

            $form->setId($id);
            $form->setFormName("CampaignForm");

//            dump($this->devices->);

            $form
                ->setCampaignEntity($entity)
                ->setDevices($this->devices)
                ->setDevicesGroups($this->devicesGroups);

            $form->create();
            $form->bootstrap3Render();
            $form->bindEntity($entity);

//            $form->onError[] = function (BaseForm $form) {
//                dump($form);
//                dump($form->getErrors());
//                die();
//            };


            $form->onSuccess[] = function (BaseForm $form, $values) {
                Debugger::barDump("onSuccess ");

//                dump($values);
//                die();



                /** @var SubmitButton $changeTemplateSubmit */
//                $changeTemplateSubmit = $form['changeTemplateSubmit'];

                /** @var SubmitButton $sendSubmit */
                $sendSubmit = $form['sendSubmit'];

//                if ($changeTemplateSubmit->isSubmittedBy()) {
//
//                    $this->redrawControl('items');
//
//                };




                if ($sendSubmit->isSubmittedBy()) {

                    /** @var CampaignEntity $entity */
                    $entity = $form->getEntity();



//                                    dump($entity);
//                                    dump($values);
//                                    die();

                    foreach ($values->mediaData as $key => $value) {

                        if (isset($value->file)) {
                            $mediumDataEntity = null;
                            if (is_numeric($key)) {
                                foreach ($entity->getMediaData() as $_mediumDataEntity) {
                                    if ($_mediumDataEntity->getId() == $key) {
                                        $mediumDataEntity = $_mediumDataEntity;
                                        break;
                                    }
                                }

                            } elseif (preg_match('%_new_(?<id>\d+)%', $key, $matches)) {
                                $collection = $entity->getMediaData();
                                $mediumDataEntity = $collection[$matches['id']];
                            }

                            $this->mediaDataFacade->saveImage($mediumDataEntity, $value->file);
                        }
                    }



//                    die("Data");




                    $em = $form->getEntityMapper()->getEntityManager();
                    $em->persist($entity)->flush();
                    $this->flashMessage('Přidáno', 'success');


                    if ($this->presenter->isAjax()) {
                        $this->redrawControl('items');
                        $this->redrawControl('campaigns');

                    } else {
                        $this->redirect('this');
                    }


                };


            };


            return $form;
        });


    }


    protected function createComponentCampaignForm()
    {
        $form = $this->campaignFormFactory->create();

        $entity = new CampaignEntity();
        $form->setCampaignEntity($entity);

        $form->create();
        $form->bootstrap3Render();
        $form->bindEntity($entity);
        $form->onSuccess[] = function (BaseForm $form) {

            $this->flashMessage('Přidáno');

            $this->ajaxRedirect();
        };

        return $form;
    }


    /**
     * @return CampaignEntity[]
     */
    public function getRows()
    {
        if (!$this->rows) {
            $query = (new CampaignQuery());

            if (!$this->user->isAllowed('Cms:Campaign', 'listAllCampaigns')) {
                $query->byUser($this->user);
            }


            if ($this->campaignRepository->existFilterActive()) {
                $filterActive = $this->campaignRepository->getFilterActive();

                switch ($filterActive) {
                    case true:
                        $query->isActive();
                        break;

                    case false:
                        $query->isNotActive();
                        break;
                }
            }

            $this->setRows($this->campaignRepository->fetch($query));
        }

        return $this->rows;
    }


    /**
     * @param CampaignEntity[] $rows
     *
     * @return $this
     */
    public function setRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->getId()] = $row;
        }

        $this->rows = $_rows;
        return $this;
    }


    /**
     * @param DeviceEntity[] $devices
     *
     * @return $this
     */
    public function setDevices($devices)
    {
        $this->devices = $devices;
        return $this;
    }

    /**
     * @param DeviceGroupEntity[] $devicesGroups
     *
     * @return $this
     */
    public function setDevicesGroups($devicesGroups)
    {
        $this->devicesGroups = $devicesGroups;
        return $this;
    }




}