<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\CampaignFilterTagsControl;
use CmsModule\Controls\CampaignsFilterControl;
use CmsModule\Controls\FlashMessageControl;
use CmsModule\Controls\ICampaignFilterTagsControlFactory;
use CmsModule\Controls\ICampaignsFilterControlFactory;
use CmsModule\Controls\ITemplateControlFactory;
use CmsModule\Controls\TemplateControl;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Entities\TemplateEntity;
use CmsModule\Facades\CampaignFacade;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\MediaDataFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\CampaignForm;
use CmsModule\Forms\ICampaignFormFactory;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\Queries\CampaignQuery;
use CmsModule\Repositories\TemplateRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Strings;
use Tracy\Debugger;
use Ublaboo\ImageStorage\ImageStoragePresenterTrait;

class CampaignPresenter extends BasePresenter
{

    use ImageStoragePresenterTrait;

    /** @var ICampaignsFilterControlFactory @inject */
    public $campaignsFilterControlFactory;

    /** @var ICampaignFilterTagsControlFactory @inject */
    public $campaignFilterTagsControlFactory;

    /** @var ITemplateControlFactory @inject */
    public $templateControlFactory;

    /** @var ICampaignFormFactory @inject */
    public $campaignFormFactory;


    /** @var CampaignFacade @inject */
    public $campaignFacade;

    /** @var MediaDataFacade @inject */
    public $mediaDataFacade;

    /** @var DeviceFacade @inject */
    public $deviceFacade;


    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    /** @var TemplateRepository @inject */
    public $templateRepository;

    /** @persistent */
    public $campaign;


    private $devices;

    private $devicesGroups;


    /**
     * @param $nestedData
     * @param $elementId
     */
    public function handleItemsNested($nestedData, $elementId)
    {
        $nestedData = json_decode($nestedData);

        $sortData = array_map(function ($data) {
            return $data->id;
        }, $nestedData);

        $positionedData = array_flip($sortData);

        $rows = $this->getCampaigns();
        $entity = $rows[$elementId]['entity'];
        $indexRows = array_values($rows);
        $targetRow = $indexRows[$positionedData[$elementId]]['entity'];

        $targetPosition = $targetRow->position;
        $em = $this->campaignFacade->getEntityManager();

        $entity->position = $targetPosition;
        $em->persist($entity)->flush();

        $this->payload->_nested_success = true;
        $this->ajaxRedirect('this', null, ['items', 'flash']);
    }


    public function handleModalDeviceFormInDevicePage()
    {
        $translator = $this->translateMessage();
        $this->deviceFacade->setNewDevice();

        $title      = $translator->translate('devicePage.management');
        $this->flashMessage($translator->translate("devicePage.addNewDevice"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('Device:');
    }


    public function handleModalDeviceGroupFormInDevicePage()
    {
        $translator = $this->translateMessage();
        $this->deviceFacade->setNewDeviceGroup();

        $title      = $translator->translate('devicePage.management');
        $this->flashMessage($translator->translate("devicePage.addNewGroup"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('DeviceGroup:');
    }


    public function handleSetFilter($active)
    {
        $this->campaignFacade->getCampaignRepository()->setFilterActive($active);
        $filter = $this->campaignFacade->getCampaignRepository()->getFilterActive();

        $message = "set";
        if ($filter === "1") {
            $message = "setActives";

        } elseif ( $filter === "0") {
            $message = "setNonActives";

        } elseif ( $filter === null) {
            $message = "setAll";
        }

        $translator = $this->translateMessage();
        $title      = $translator->translate('campaignPage.management');
        $this->flashMessage($translator->translate("campaignPage.filter.$message"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('this', null, ['campaigns', 'flash']);
    }


    public function handleResetSelectCampaign()
    {
        $this->campaign = null;
        $this->ajaxRedirect('this');
    }



    public function handleDetail($id)
    {
        $this->template->toggle_detail = $id;
        $this->payload->_toggle_detail = $id;
        $this->campaignFacade->getCampaignRepository()->setOpenDetailCampaign($id);

        $this->ajaxRedirect('this', null, ['items']);
    }


    public function handleToggleActive($cid, $checked)
    {
        $campaignRepository = $this->campaignFacade->getCampaignRepository();

        /** @var CampaignEntity $element */
        if ($element = $campaignRepository->find($cid)) {

            $this->campaignFacade->setActive($element, $checked);

            $translator = $this->translateMessage();
            $message    = $element->isActive()
                ? $translator->translate("campaignPage.campaign_active", null, ['name' => $element->getName()])
                : $translator->translate("campaignPage.campaign_non_active", null, ['name' => $element->getName()]);

            $title = $translator->translate('campaignPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        }

        if ($campaignRepository->existFilterActive()) {

            if (($filterActive = $campaignRepository->getFilterActive()) !== null) {
                $this->payload->_switchery_redraw = true;
                $this->payload->_filter_toggle = true;
                $this->ajaxRedirect('this', null, ['campaigns', 'filter', 'flash']);
                return;
            }
        }

        $this->ajaxRedirect('this', null, ['filter', 'flash']);
    }


    /**
     * @todo upravit na facade
     *
     * @param $cid
     * @param $tid
     */
    public function handleChangeTemplate($cid, $tid, array $values = array())
    {
        /** @var CampaignEntity $campaign */
        if ($campaign = $this->campaignFacade->getCampaignRepository()->find($cid)) {

            $em = $this->campaignFacade->getCampaignRepository()->getEntityManager();

            /** @var TemplateEntity $template */
            if ($template = $em->getRepository(TemplateEntity::getClassName())->find($tid)) {

                $campaign->setTemplate($template);

                if ($mediaDataEntities = $em->getRepository(MediumDataEntity::getClassName())->findBy(['campaign' => $campaign])) {
                    $em->remove($mediaDataEntities);

                    $translator = $this->translateMessage();
                    $message = $translator->translate('campaignPage.template_data_deleted');
                    $title   = $translator->translate('campaignPage.management');

                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
                    $this->redrawControl('flash');
                }


                /** @var CampaignForm $form */
/*
                $form       = $this["campaignsDetailForm-$cid"];
                $components = $form->components;

                $ignored = ['template', 'devices', 'realizedFrom'];
                $assocValues = [];

                foreach ($values as $value) {
                    $name = $value['name'];
                    if (Strings::endsWith($name, '[]')) {
                        $name = str_replace('[]', '', $name);
                        $assocValues[$name][] = $value['value'];

                    } else {
                        $assocValues[$name] = $value['value'];
                    }
                }

                foreach ($components as $name => $component) {
                    if (isset($assocValues[$name]) && !in_array($name, $ignored) ) {
                        $component->value = $assocValues[$name];

                        Debugger::barDump($name);

                        $component->validate();
                        if (!$component->hasErrors()) {
                            $campaign->$name = $component->value;
                        }

//                        Debugger::barDump($q = $component->hasErrors());

                    }
                }

//                $form->setCampaignEntity($campaign);
*/
                $em->persist($campaign)->flush();
            }

        }

        $this->template->change_template = $cid;
        $this->payload->_change_template = $cid;

        $this->ajaxRedirect('this', null, ['items', 'flash']);
    }


    public function handleRemoveMediumData($mid)
    {
        $openCampaignId = $this->campaignFacade->getRepository()->getOpenDetailCampaign();

        $em = $this->campaignFacade->getCampaignRepository()->getEntityManager();

        /** @var MediumDataEntity $mediumDataEntity */
        if ($mid && $mediumDataEntity = $em->getRepository(MediumDataEntity::class)->find($mid)) {

            if ($mediumDataEntity->getCampaign()->getId() == $openCampaignId) {
                $filename = $mediumDataEntity->getFileName();

                if ($this->mediaDataFacade->removeFileFromMedium($mediumDataEntity)) {
                    $em->persist($mediumDataEntity)->flush();

                    $translator = $this->translateMessage();
                    $message    = $translator->translate('campaignPage.file_deleted', null, ['name' => $filename]);
                    $title      = $translator->translate('campaignPage.management');

                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
                }
            }
        }

        $this->ajaxRedirect('this', null, ['flash']);
    }


    public function actionInit()
    {
        $this->campaignFacade->initPositions();

    }

    public function actionDefault()
    {
        $query   = $this->getUserAllowedDevicesQuery();
        $devices = $this->deviceRepository->fetch($query)->count();

        if (($devices) == 0 ) {
            $message    = $this->translator->domain('messages')->translate('campaignPage.device_not_found');
            $title      = $this->translator->domain('messages')->translate('campaignPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            //$this->ajaxRedirect('Device:', null, ['flash']);
        }
        $this->template->devices = $devices;

    }



    public function renderDefault()
    {

        $campaignRepository = $this->campaignFacade->getCampaignRepository();

        $query = $this->getUserAllowedCampaignsQuery();
//        $query->withMediaDataCount();

        $rows = $campaignRepository->fetch($query);

        $total = $rows->count(); //        $total = $rows->getTotalCount();
        $active = $nonActive = 0;

        foreach ($rows as $row) {
            if ($row['entity']->active) $active++;
            if (!$row['entity']->active) $nonActive++;
        }

        $this->template->allCampaignCount       = $total;
        $this->template->activeCampaignCount    = $active;
        $this->template->nonActiveCampaignCount = $nonActive;
        $this->template->campaigns              = $this->getCampaigns();

        if ($this->campaignFacade->isNewCampaignSelectDevice()) {
            $this->template->newCampaignSelectDevice = $this->campaignFacade->getNewCampaignSelectDevice();
        }

        $this->checkFormUploadValid();
    }


    private function getUserAllowedDevicesQuery()
    {
        return $this->deviceFacade->getDeviceRepository()->getUserAllowedQuery($this->user);
    }


    /**
     * @return CampaignQuery
     */
    private function getUserAllowedCampaignsQuery()
    {
        $query = $this->campaignFacade->getCampaignRepository()->getUserAllowedQuery(
            $this->user,
            $this->deviceRepository->getFilterDevice(),
            $this->deviceGroupRepository->getFilterDeviceGroup(),
            $this->campaign);

        return $query;
    }


    /**
     * allowed campaigns for user
     *
     * @return CampaignEntity[]
     */
    private function getCampaigns()
    {
        $rows = null;

        if (null === $rows) {

            $query = $this->getUserAllowedCampaignsQuery();
            $query->withMediaDataCount();

            if ($this->campaignFacade->getCampaignRepository()->existFilterActive()) {
                $filterActive = $this->campaignFacade->getCampaignRepository()->getFilterActive();

                switch ($filterActive) {
                    case true:
                        $query->isActive();
                        break;

                    case false:
                        $query->isNotActive();
                        break;
                }
            }

            $query->orderByPosition();
            $rows = $this->pairsRows($this->campaignFacade->getCampaignRepository()->fetch($query));
        }

        return $rows;
    }


    /**
     * @todo method to getCampaigns
     *
     * @param CampaignEntity[] $rows
     *
     * @return CampaignEntity[]
     */
    private function pairsRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row['entity']->id] = $row;
        }

        return $_rows;
    }


    /**
     * check all uploaded files -> post_max_size
     */
    private function checkFormUploadValid()
    {
        $files = $this->getPresenter()->getRequest()->getFiles();

        if (count($files) !== 1 || !isset($files['upload'])) {
            // If post_max_size is exceeded both $_POST and $_FILES are empty.
            if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {

                $translator = $this->translateMessage();
                $message    = $translator->translate('campaignPage.upload_error');
                $title      = $translator->translate('campaignPage.management');

                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);

                $id = $this->campaignFacade->getCampaignRepository()->getOpenDetailCampaign();
                $this->template->toggle_detail = $id;
                $this->payload->_toggle_detail = $id;

                /** @var CampaignForm $form */
                $form = $this["campaignsDetailForm-$id"];

                $message = $translator->translate('campaignPage.max_upload_limit_info', ini_get('post_max_size'));
                $form->addError($message);

                $this->ajaxRedirect('this', null, ['items', 'flash']);
            }

            // případ který by neměl nastat, zatím zde vyhazuji BadRequestException
        }
    }



    /**
     * new campaign
     *
     * @return \CmsModule\Forms\CampaignForm
     */
    protected function createComponentCampaignForm($name)
    {
        $devices       = $this->getDevices();
        $devicesGroups = $this->getDevicesGroups();
        $templates     = $this->templateRepository->getTemplates($this->user);

        $form = $this->campaignFormFactory->create();
        $form
            ->setTranslator($this->translator->domain("messages.forms.campaignsDetailForm"))
            ->setFormName("campaignForm")
            ->setCampaignEntity($entity = new CampaignEntity())
            ->setUserEntity($this->userEntity)
            ->setTemplates($templates)
            ->setDevices($devices)
            ->setDevicesGroups($devicesGroups);

        $form->create();
        $form->bootstrap3Render();
        $form->bindEntity($entity);

        $defaults = [];
        if ($this->campaignFacade->isNewCampaignSelectDevice()) {
            $defaults['devices'] = [$this->campaignFacade->getNewCampaignSelectDevice()];
            $this->campaignFacade->cleanNewCampaignSelectDevice();
        }
        $form->setDefaults($defaults);
        $form->onSuccess[] = function (BaseForm $form, $values) {

            /** @var SubmitButton $sendSubmit */
            $sendSubmit = $form['sendSubmit'];

            if ($sendSubmit->isSubmittedBy()) {

                /** @var CampaignEntity $entity */
                $entity = $form->getEntity();
                $this->campaignFacade->getEntityManager()->persist($entity)->flush();

                $translator = $this->translateMessage();
                $message    = $translator->translate('campaignPage.campaign_added', null, ['name' => $values->name]);
                $title      = $translator->translate('campaignPage.management');

                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
                $form->setValues([], true);
            }

            $this->payload->_switchery_redraw = true;
            $this->ajaxRedirect('this', null, ['campaigns', 'wrapperModal', 'campaignFormModal', 'filter', 'flash']);
        };

        return $form;
    }


    /**
     * display panel campaigns form
     * @param $name
     *
     * @return Multiplier
     */
    protected function createComponentCampaignsForm($name)
    {
        $self = $this;

        return new Multiplier(function ($id) use ($self, $name) {


            /** @var CampaignEntity $entity */
            $entity = $self->template->campaigns[$id]['entity'];

            $form = new Form();

            $form->addCheckbox('active', $this->translator->translate('messages.forms.campaignsDetailForm.active'))
                ->setDisabled($this->user->isAllowed(CampaignForm::class, 'edit') == false)
                ->setAttribute('class', 'js-switch')
                ->setAttribute('data-size', 'small');

            $form->getElementPrototype()
                ->addAttributes([
                    'class' => 'ajax',
                    'data-name' => "CampaignForm",
                    'data-id' => $id,
                    'data-ajax' => "false",
                ]);

            $form->setDefaults([
                'active' => $entity->isActive(),
            ]);

            return $form;
        });
    }


    /**
     * campaign detail form
     * @param $name
     *
     * @return Multiplier
     */
    protected function createComponentCampaignsDetailForm($name)
    {
        $self = $this;

        $campaignRepository = $this->campaignFacade->getCampaignRepository();

        $devices = $this->getDevices();
        $devicesGroups = $this->getDevicesGroups();

        return new Multiplier(function ($id) use ($self, $name, $devices, $devicesGroups, $campaignRepository) {

            /** @var CampaignEntity $entity */
//            $entity = $self->template->campaigns[$id]['entity'];
            $entity = $this->getCampaigns()[$id]['entity'];
            $entity->synchronizeMediaDataFromTemplate();

            $mediumData = $entity->getMediaData();

            $form = $this->campaignFormFactory->create();

            $form->setId($id);
            $form->setFormName("campaignDetailForm");

            $templates = $this->templateRepository->getTemplates($this->user);

            $form
                ->setTranslator($this->translator->domain("messages.forms.$name"))
                ->setUserEntity($this->userEntity)
                ->setCampaignEntity($entity)
                ->setTemplates($templates)
                ->setDevices($devices)
                ->setDevicesGroups($devicesGroups);

            $form->create();
            $form->bootstrap3Render();
            $form->bindEntity($entity);

            $deviceList = [];
            foreach ($entity->getDevices() as $device) {
                if (in_array($device->getId(), array_keys($devices))) {
                    $deviceList[] = $device->getId();
                }
            }

            $deviceGroupList = [];
            foreach ($entity->getDevicesGroups() as $devicesGroup) {
                if (in_array($devicesGroup->getId(), array_keys($devicesGroups))) {
                    $deviceGroupList[] = $devicesGroup->getId();
                }
            }

            $form->setDefaults([
                'devices' => $deviceList,
                'devicesGroups' => $deviceGroupList,
            ]);

            $form->onError[] = function (BaseForm $form) {
                $entity = $form->getValues();

                $errors = implode("<br>", $form->getErrors());

                $this->translator->translate('messages.forms.campaignsDetailForm.active');

                $translator = $this->translateMessage();
                $message    = $translator->translate('campaignPage.form_has_error', null, ['name' => $entity->name, 'errors' => $errors]);
                $title      = $translator->translate('campaignPage.management');

                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);

                $openCampaignId = $this->campaignFacade->getCampaignRepository()->getOpenDetailCampaign();

                $this->template->change_template = $openCampaignId;
                $this->payload->_change_template = $openCampaignId;

                $this->ajaxRedirect('this', null, [/*'items',*/'flash']);
            };


            $form->onSuccess[] = function (BaseForm $form, $values) use ($devices, $devicesGroups) {

                /** @var SubmitButton $changeTemplateSubmit */
//                $changeTemplateSubmit = $form['changeTemplateSubmit'];

                /** @var SubmitButton $sendSubmit */
                $sendSubmit = $form['sendSubmit'];

                if ($sendSubmit->isSubmittedBy()) {

                    /** @var CampaignEntity $entity */
                    $entity = $form->getEntity();

                    $selectDevices       = (array)$values->devices;
                    $selectDevicesGroups = (array)$values->devicesGroups;

                    $this->campaignFacade->updateDevices($entity, $devices, $selectDevices);
                    $this->campaignFacade->updateDevicesGroups($entity, $devicesGroups, $selectDevicesGroups);

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

                            $this->mediaDataFacade->saveFileUpload($mediumDataEntity, $value->file);
                        }
                    }

                    /*
                     * clear empty mediaData
                     */
                    /*
                    foreach ($entity->getMediaData() as $mediumDataEntity) {
                        if (null == $mediumDataEntity->getType() ) {
                            $entity->removeMediumData($mediumDataEntity);
                        }
                    }
                    */

//                    die("Data");



                    $this->campaignFacade->getEntityManager()->persist($entity)->flush();

                    $translator = $this->translateMessage();
                    $message    = $translator->translate('campaignPage.campaign_updated', null, ['name' => $values->name]);
                    $title      = $translator->translate('campaignPage.management');

                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

                    $deviceFilter = $this->deviceRepository->getFilterDevice();
                    $deviceGroupFilter = $this->deviceGroupRepository->getFilterDeviceGroup();

                    $this->payload->_success = true;

                    /*
                     * if some filter is set, must re-validate more snippets
                     */
                    if ($deviceFilter || $deviceGroupFilter) {

                        $this->payload->_switchery_redraw = true;
                        $this->ajaxRedirect('this', null, ['campaigns', 'items', 'filter', 'flash']);

                    } else {

                        /*
                         * if not device filter define, can re-validate less snippets
                         */
                        $this->ajaxRedirect('this', null, ['items', 'flash']);
                    }

                };
            };

            return $form;
        });
    }




    /**
     * filter device|deviceGroup control
     *
     * @return \CmsModule\Controls\CampaignsFilterControl
     */
    protected function createComponentCampaignsFilterControl()
    {
        $control = $this->campaignsFilterControlFactory->create();
        $control->onFilter[] = function ($filter) {

            /** @var CampaignFilterTagsControl $tagsControl */
            $tagsControl = $this['campaignFilterTagsControl'];
            $tagsControl->onFiltered($filter);

            $this->payload->_switchery_redraw = true;
            $this->ajaxRedirect('this', null, ['campaigns', 'filter']);
        };

        return $control;
    }


    /**
     * filter deviceTags|deviceGroupTags control
     *
     * @return \CmsModule\Controls\CampaignFilterTagsControl
     */
    protected function createComponentCampaignFilterTagsControl()
    {
        $control = $this->campaignFilterTagsControlFactory->create();
        $control->onDeviceFilter[] = function ($filter) {

            /** @var CampaignsFilterControl $filterControl */
            $filterControl = $this['campaignsFilterControl'];
            $filterControl->onDeviceFiltered($filter);
        };

        $control->onDeviceGroupFilter[] = function ($filter) {

            /** @var CampaignsFilterControl $filterControl */
            $filterControl = $this['campaignsFilterControl'];
            $filterControl->onDeviceGroupFiltered($filter);
        };

        return $control;
    }


    /**
     * add new template popup control
     *
     * @return \CmsModule\Controls\TemplateControl
     */
    protected function createComponentTemplateControl()
    {
        $control = $this->templateControlFactory->create();
        $control->onTemplateFormSuccess[] = function (TemplateEntity $entity, TemplateControl $templateControl) {

            $entity->updateUser($this->userEntity);

            $em = $this->campaignFacade->getEntityManager();
            $em->persist($entity)->flush();

            $openCampaignId = $this->campaignFacade->getCampaignRepository()->getOpenDetailCampaign();

            $this->payload->lastTemplateId = $entity->getId();
            $this->payload->lastTemplateName = $entity->getName();

            $this->template->change_template = $openCampaignId;
            $this->payload->_change_template = $openCampaignId;

            $translator = $this->translateMessage();
            $message    = $translator->translate('campaignPage.template_added', null, ['name' => $entity->getName()]);
            $title      = $translator->translate('campaignPage.management');

            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
            $this->ajaxRedirect('this', null, ['_campaignFormModal', 'flash']);
        };

        return $control;
    }


    /**
     * @return DeviceEntity[]|null
     */
    public function getDevices()
    {
        if (null === $this->devices) {
            $this->devices = $this->deviceRepository->getAssocRows($this->deviceRepository->fetch($this->deviceRepository->getUserAllowedQuery($this->user))->getIterator());
        }

        return $this->devices;
    }

    /**
     * @return DeviceGroupEntity[]|null
     */
    public function getDevicesGroups()
    {
        if (null === $this->devicesGroups) {
            $this->devicesGroups = $this->deviceFacade->getAllowedDevicesGroups($this->user);
        }

        return $this->devicesGroups;
    }

}