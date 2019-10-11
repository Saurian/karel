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
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Entities\UsersGroupEntity;
use CmsModule\Facades\CalendarFacade;
use CmsModule\Facades\CampaignFacade;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\MediaDataFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\CampaignForm;
use CmsModule\Forms\ICampaignFormFactory;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\Queries\CampaignQuery;
use Devrun\CmsModule\Controls\DataGrid;
use Devrun\Php\PhpInfo;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Ublaboo\ImageStorage\ImageStoragePresenterTrait;

class CampaignPresenter extends BasePresenter
{

    use ImageStoragePresenterTrait;

    /** @var ICampaignsFilterControlFactory @inject */
    public $campaignsFilterControlFactory;

    /** @var ICampaignFilterTagsControlFactory @inject */
    public $campaignFilterTagsControlFactory;

    /** @var ICampaignFormFactory @inject */
    public $campaignFormFactory;


    /** @var CalendarFacade @inject */
    public $calendarFacade;

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


    /** @var int @persistent */
    public $campaign;


    private $devices;

    private $devicesGroups;


    private $editCampaign;


    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleGenerateTest()
    {
        $translator = $this->translateMessage();

        if ($usersGroup = $this->userEntity->getGroup()) {
            $this->calendarFacade->generate($usersGroup);

            $title      = $translator->translate('campaignPage.management');
            $this->flashMessage($translator->translate("campaignPage.plan_generated"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->ajaxRedirect('this', 'calendarControl', 'flash');

        } else {
            $title      = $translator->translate('campaignPage.management');
            $this->flashMessage($translator->translate("campaignPage.user_group_not_found"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect('this', 'calendarControl', 'flash');
        }

        /**
         * @todo prevent redirect
         */
        $this->redirect('this');
    }


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


    public function handleCalendarCalc()
    {
        dump("ASDD");
        die(__METHOD__);
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

        $this->template->editCampaign = $this->campaign;

        if ($this->campaignFacade->isNewCampaignSelectDevice()) {
            $this->template->newCampaignSelectDevice = $this->campaignFacade->getNewCampaignSelectDevice();
        }

//        $this->checkFormUploadValid();
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
     * new and edit campaign form
     *
     * @return \CmsModule\Forms\CampaignForm
     */
    protected function createComponentCampaignForm()
    {
        $devices       = $this->getDevices();
        $devicesGroups = $this->getDevicesGroups();
//        $templates     = $this->templateRepository->getTemplates($this->user);

        $entity = $this->campaign
            ? $this->campaignFacade->getRepository()->find($this->campaign)
            : new CampaignEntity();

/*        $devicesSelect= [];

        foreach ($entity->getDevices() as $device) {
            $devicesSelect[] = $device->getId();
        }

        $devicesGroupsSelect= [];

        foreach ($entity->getDevicesGroups() as $devicesGroup) {
            $devicesGroupsSelect[] = $devicesGroup->getId();
        }*/




        $form = $this->campaignFormFactory->create();
        $form
            ->setTranslator($this->translator->domain("messages.forms.campaignsDetailForm"))
            ->setFormName("campaignForm")
//            ->setCampaignEntity($entity)
            ->setUserEntity($this->userEntity)
//            ->setTemplates($templates)
            ->setDevices($devices)
            ->setDevicesGroups($devicesGroups);

        $form->create();
        $form->bootstrap3Render();
        $form->bindEntity($entity);

        $defaults = [];
        if ($this->campaignFacade->isNewCampaignSelectDevice()) {
//            $defaults['devices'] = [$this->campaignFacade->getNewCampaignSelectDevice()];
            $this->campaignFacade->cleanNewCampaignSelectDevice();
        }

//        $defaults['devices'] = $devicesSelect;
//        $defaults['devicesGroups'] = $devicesGroupsSelect;

        $form->setDefaults($defaults);
        $form->onSuccess[] = function (BaseForm $form, $values) {

            /** @var SubmitButton $sendSubmit */
            $sendSubmit = $form['sendSubmit'];

            if ($sendSubmit->isSubmittedBy()) {

                /** @var CampaignEntity $entity */
                $entity = $form->getEntity();
                $newEntity = $entity->getId() == null;

                /**
                 * porovnáme value devices s entitou, nesouhlasné device smažeme
                 */
                foreach ($entity->getDevices() as $device) {
                    if (! in_array($device->getId(), (array) $values->devices)) {
                        $entity->removeDevice($device);
                    }
                }
                foreach ($entity->getDevicesGroups() as $devicesGroup) {
                    if (! in_array($devicesGroup->getId(), (array) $values->devicesGroups)) {
                        $entity->removeDeviceGroup($devicesGroup);
                    }
                }
                foreach ($entity->getMetrics() as $metricEntity) {
                    if (! in_array($metricEntity->getId(), (array) $values->metrics)) {
                        $entity->removeMetric($metricEntity);
                    }
                }

//                Debugger::barDump($values);
//                Debugger::barDump($entity);

                $this->campaignFacade->getEntityManager()->persist($entity)->flush();

                $translator = $this->translateMessage();
                $message    = $translator->translate('campaignPage.campaign_added', null, ['name' => $values->name]);
                $title      = $translator->translate('campaignPage.management');

                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
                $form->setValues([], true);

                if ($newEntity) {
                    $this['campaignGridControl']->redrawControl();

                } else {
                    $this['campaignGridControl']->redrawItem($entity->getId());
                }

            }


            $this->campaign = null;
            $this->payload->url = $this->link('this');
            $this->payload->_switchery_redraw = true;
            $this->ajaxRedirect('this', null, ['campaignFormModal', 'flash']);
        };

        return $form;
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

            /** @var DataGrid $grid */
            $grid = $this['campaignGridControl'];

            if ($filter) {
                $grid->setFilter($filter);
            }

            $grid->reload();
            $grid->redrawControl();


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
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnStatusException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentCampaignGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);
        $grid->setItemsPerPageList([20, 30, 50]);
        $grid->setRememberState(true);
        $grid->setRefreshUrl(true);


        if ($this->getUser()->isAllowed('Cms:Campaign', 'listAllCampaigns')) {
            $model = $this->campaignFacade->getRepository()->createQueryBuilder('e')
                ->select('e');

        } else {
            $model = $this->campaignFacade->getRepository()->createQueryBuilder('e')
                ->select('e')
                ->leftJoin('e.devices', 'd')
                ->leftJoin('e.devicesGroups', 'dg')
                ->leftJoin('d.devicesUsers', 'du')
                ->leftJoin('dg.devicesGroupsUsers', 'dgu')
                ->andWhere('du.id = :user OR dgu.id = :user')->setParameter('user', $this->getUser()->getId());
        }


        $deviceEntities = $this->deviceRepository->getCachedResult($this->deviceRepository->getUserAllowedQueryBuilder($this->getUser()));
        $deviceGroupsEntities = $this->deviceGroupRepository->getCachedResult($this->deviceGroupRepository->getUserAllowedQueryBuilder($this->getUser()));

        $devices = $this->deviceRepository->getPairs($deviceEntities);
        $deviceGroups = $this->deviceGroupRepository->getPairs($deviceGroupsEntities);


        $grid->setDataSource($model);

        $grid->addColumnText('tag', 'Štítek')
            ->setFitContent()
            ->setSortable()
            ->setRenderer(function (CampaignEntity $row) {
                $html = $row->tag
                    ? Html::el('div')->addAttributes(['class' => "{$row->tag}", 'style' => "width: 60px; height: 25px; "])
                    : Html::el('div')->addAttributes(['style' => "background-color: #F2F2F2; width: 60px; height: 25px; "]);
                return $html;
            });

        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnDateTime('realizedFrom', 'Plán od')
            ->setFormat('j. n. Y H:i')
            ->setFitContent()
            ->setAlign('center')
            ->setSortable()
            ->setRenderer(function (CampaignEntity $campaignEntity) {
                $el = ($campaignEntity->getRealizedFrom() < new DateTime() && $campaignEntity->getRealizedTo() < new DateTime())
                    ? 'strike'
                    : 'span';

                $html = Html::el($el)->setText(\Nette\DateTime::from($campaignEntity->getRealizedFrom())->format('j. n. Y H:i'));
                return $html;
            })
            ->setFilterDate()
            ->setCondition(function (QueryBuilder $qb, $value) {
                $date = DateTime::createFromFormat("d. m. Y", $value)->setTime(0,0,0);
                $qb->andWhere('e.realizedFrom >= :realizedFrom')->setParameter('realizedFrom', $date);
            });


        $grid->addColumnDateTime('realizedTo', 'Plán do')
            ->setFitContent()
            ->setFormat('j. n. Y H:i')
            ->setAlign('center')
            ->setSortable()
            ->setRenderer(function (CampaignEntity $campaignEntity) {
                $el = ($campaignEntity->getRealizedFrom() < new DateTime() && $campaignEntity->getRealizedTo() < new DateTime())
                    ? 'strike'
                    : 'span';

                $html = Html::el($el)->setText(\Nette\DateTime::from($campaignEntity->getRealizedTo())->format('j. n. Y H:i'));
                return $html;
            })
            ->setFilterDate()
            ->setCondition(function (QueryBuilder $qb, $value) {
                $date = DateTime::createFromFormat("d. m. Y", $value)->setTime(23,59,59);
                $qb->andWhere('e.realizedTo <= :realizedTo')->setParameter('realizedTo', $date);
            });


        $statusList = array('' => 'Vše', '0' => 'Neaktivní', '1' => 'Aktivní');

        $grid->addColumnStatus('active', 'Stav')
            ->setSortable()
            ->setFitContent()
            ->addOption(0, 'Neaktivní')
            ->setClass('btn-default')
            ->endOption()
            ->addOption(1, 'Aktivní')
            ->setIcon('check')
            ->setClass('btn-success')
            ->endOption()
            ->setFilterSelect($statusList);

        $grid->getColumn('active')
            ->onChange[] = function ($id, $new_value) {

            /** @var CampaignEntity $entity */
            $entity = $this->campaignFacade->getRepository()->find($id);
            $entity->active = $new_value;
            $this->campaignFacade->getEntityManager()->persist($entity)->flush();

            if ($this->isAjax()) $this['campaignGridControl']->redrawItem($id); else $this->redirect('this');
        };


        $grid->addFilterMultiSelect('devices', 'Zařízení', $devices, 'd.id')
            ->setCondition(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $value) use ($grid) {
                if ($devicesGroupFilter = $grid->getFilter('deviceGroups')->getValue()) {
                    $queryBuilder->andWhere('d.id IN (:devices) OR dg.id IN (:deviceGroups)')
                        ->setParameter('devices', $value)
                        ->setParameter('deviceGroups', $devicesGroupFilter);

                } else {
                    $queryBuilder->andWhere('d.id IN (:devices)')->setParameter('devices', $value);
                }
            });

        $grid->addFilterMultiSelect('deviceGroups', 'Skupiny', $deviceGroups, 'dg.id')
            ->setCondition(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $value) use ($grid) {
                if (!$deviceFilter = $grid->getFilter('devices')->getValue()) {
                    $queryBuilder->andWhere('dg.id IN (:deviceGroups)')->setParameter('deviceGroups', $value);
                }
            });



        $grid->onResetFilter[] = function () {

            /** @var CampaignsFilterControl $filterControl */
            $filterControl = $this['campaignsFilterControl'];
            $filterControl->onRedraw();
        };


        $grid->addAction('edit', 'Upravit', 'editCampaign!')
            ->setIcon('pencil fa-1x')
            ->setDataAttribute('backdrop', 'static')
            ->setDataAttribute('target', '.addCampaignModal')
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
            ->setTitle($this->translateMessage()->translate('devicePage.editDevice'))
            ->setClass('btn btn-xs btn-info');


        $grid->addAction('delete', '', 'deleteCampaign!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat kampaň `{$item->name}`?";
            });

        $grid->setTemplateFile(__DIR__ . '/templates/Campaign/#datagrid_campaign.latte');

        return $grid;
    }


    /**
     * Media Grid
     *
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentMediaGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);

        $grid->setPagination(false);

        $model = $this->mediaDataFacade->getRepository()->createQueryBuilder('e')
            ->select('e')
            ->addSelect('c')
            ->join('e.campaign', 'c')
            ->where('c.id = :campaign')->setParameter('campaign', $this->campaign)
//            ->addOrderBy('e.position')
        ;

        $grid->setDataSource($model);


        $wwwDir = $this->context->getParameters()['wwwDir'];

        $grid->addColumnText('identifier', '')
            ->setFitContent()
            ->setRenderer(function (MediumDataEntity $entity) use ($wwwDir) {
                $link = $this->imageStorage->fromIdentifier([ $entity->getIdentifier()]);
                $img = $this->imageStorage->fromIdentifier([ $entity->getIdentifier(), '80x50', 'exact']);

                $a = Html::el('a')->href(DIRECTORY_SEPARATOR . $link->createLink())->addAttributes(['data-lightbox' => $entity->getCategory(), 'data-title' => $entity->getFileName()]);
                $img = Html::el('img')->addAttributes(['src' => DIRECTORY_SEPARATOR . $img->createLink()]);

                $a->addHtml($img);
                return $a;
            });

        $presenter = $this;


        $grid->addColumnNumber($column = 'time', 'čas')
            ->setEditableInputType('number', ['class' => 'form-control'])
            ->setEditableOnConditionCallback(function (MediumDataEntity $mediumDataEntity) {
                return $mediumDataEntity->getType() == 'image/jpeg';
            })
            ->setEditableCallback(function($id, $value) use ($grid, $presenter, $column) {
                if (Validators::is($value, $validate = 'numericint:0..65000')) {

                    if ($entity = $this->mediaDataFacade->getRepository()->find($id)) {
                        $entity->$column = $value;
                        $this->mediaDataFacade->getRepository()->getEntityManager()->persist($entity)->flush();

                        $translator = $this->translateMessage();

                        $title = $translator->translate('campaignPage.management');
                        $message = $translator->translate('medium upraveno', null );
                        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

                        $presenter->redrawControl('flash');
                        $grid->redrawItem($id);
                        return true;
                    }
                }
                $message = "input not valid [$value != $validate]";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $this->translator->translate('pexeso.admin.settings_title'), FlashMessageControl::TOAST_SUCCESS);
                $presenter->redrawControl('flash');
                $grid->reload();

                return $grid->invalidResponse($message);
            });


        $grid->addColumnText($column = 'timeType', '')
            ->setFitContent(false)
            ->setEditableInputTypeSelect(['s' => 'sec', 'min' => 'minut'], ['class' => 'form-control'])
            ->setEditableOnConditionCallback(function (MediumDataEntity $mediumDataEntity) {
                return $mediumDataEntity->getType() == 'image/jpeg';
            })
            ->setEditableCallback(function($id, $value) use ($grid, $presenter, $column) {
                if (Validators::is($value, $validate = 'string')) {

                    if ($entity = $this->mediaDataFacade->getRepository()->find($id)) {
                        $entity->$column = $value;
                        $this->mediaDataFacade->getRepository()->getEntityManager()->persist($entity)->flush();

                        $translator = $this->translateMessage();

                        $title = $translator->translate('campaignPage.management');
                        $message = $translator->translate('medium upraveno', null );
                        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

                        $presenter->redrawControl('flash');
                        $grid->redrawItem($id);
                        return true;
                    }
                }
                $message = "input not valid [$value != $validate]";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $this->translator->translate('pexeso.admin.settings_title'), FlashMessageControl::TOAST_SUCCESS);
                $presenter->redrawControl('flash');
                $grid->reload();

                return $grid->invalidResponse($message);
            });



        $grid->addColumnDateTime($column = 'keywords', 'Klíčová slova')
            ->setEditableInputType('textarea', ['class' => 'form-control'])
//            ->setEditableOnConditionCallback(function (MediumDataEntity $mediumDataEntity) {
//                return $mediumDataEntity->getType() == 'image/jpeg';
//            })
            ->setEditableCallback(function($id, $value) use ($grid, $presenter, $column) {
                if (Validators::is($value, $validate = 'string')) {

                    if ($entity = $this->mediaDataFacade->getRepository()->find($id)) {
                        $entity->$column = $value;
                        $this->mediaDataFacade->getRepository()->getEntityManager()->persist($entity)->flush();

                        $translator = $this->translateMessage();

                        $title = $translator->translate('campaignPage.management');
                        $message = $translator->translate('medium upraveno', null );
                        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

                        $presenter->redrawControl('flash');
                        $grid->redrawItem($id);
                        return true;
                    }
                }
                $message = "input not valid [$value != $validate]";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $this->translator->translate('pexeso.admin.settings_title'), FlashMessageControl::TOAST_SUCCESS);
                $presenter->redrawControl('flash');
                $grid->reload();

                return $grid->invalidResponse($message);
            });


        $grid->addColumnDateTime('type', 'Typ')
            ->setFitContent();


/*        $grid->addAction('edit', 'Upravit', 'editCampaign!')
            ->setIcon('pencil')
            ->setDataAttribute('backdrop', 'static')
            ->setDataAttribute('target', '.addDeviceModal')
            ->setDataAttribute('toggle', 'ajax-modal')
            ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
            ->setTitle($this->translateMessage()->translate('devicePage.editDevice'))
            ->setClass('btn btn-xs btn-info');*/


        $grid->addAction('delete', '', 'deleteMedium!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat medium `{$item->id}`?";
            });


        /**
         * error span message
         */
        $grid->addToolbarButton('#')
            ->setRenderer(function () {
                $html = Html::el("span")->addAttributes(['id' => 'mediaErrorMessage', 'class' => 'text-danger']);
                return $html;
            })
            ->addAttributes([
                'data-target' => '.addDeviceModal',
                'data-title' => $this->translateMessage()->translate('devicePage.edit_device_group'),
            ]);


        $grid->addToolbarButton('addMedia', 'Nahrát obsah')
            ->addAttributes([
                'data-click' => '#frm-mediaForm-files',
                'data-title' => $this->translateMessage()->translate('devicePage.edit_device_group'),
            ])
            ->setClass('addMedia btn btn-xs btn-success')
            ->setIcon('files-o');


        $grid->addGroupAction('Smazat vybrané')->onSelect[] = [$this, 'removeMedia'];




        $grid->setSortable();

        return $grid;
    }


    /**
     * @param $name
     * @return \CmsModule\Controls\CalendarControl
     */
    protected function createComponentCalendarControl()
    {
        $control = $this->calendarFacade->getCalendarControl()->create();

        $control->setUsersGroupEntity($this->userEntity->getGroup());

        return $control;
    }


    protected function createComponentMediaForm()
    {
        $form = new Form();

        $validations = [
            'image/jpeg','image/png','image/gif',
            'application/x-rar', 'application/x-rar-compressed', 'application/zip',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv',
            ];




        $form->addMultiUpload('files')
            ->addRule(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, 'Nahrejte nejvýše %d souborů', 10)
//            ->addRule(Form::IMAGE, 'ruleImage')
//            ->addRule(Form::MIME_TYPE, 'ruleZip', $validations)
//            ->addRule(Form::MIME_TYPE, 'ruleVideo', ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'])
            ->addRule(Form::MAX_FILE_SIZE, new Phrase('ruleMaxFileSize', NULL, ["size"=>sprintf("Soubor může mít maximálně %s", ini_get('upload_max_filesize'))]), PhpInfo::file_upload_max_size());


        $form->addSubmit('send');
        $form->getElementPrototype()->addAttributes(['class' => 'ajax auto-save', 'style' => 'display:none']);

        $form->onSuccess[] = function ($form, $values) use ($validations) {

            /** @var CampaignEntity $campaignEntity */
            $campaignEntity = $this->campaignFacade->getRepository()->find($this->campaign);
            $maxPosition = $this->mediaDataFacade->getRepository()->getMaxPositionInCategory($campaignEntity->getId());
            $maxPosition++;
            $unsupported = [];
            $supported = [];

            /** @var FileUpload $file */
            foreach ($values->files as $key => $file) {

                if (!in_array($file->contentType, $validations)) {
                    $unsupported[] = $file->getName();
                    continue;
                }


                $mediumEntity = $file->isImage()
                    ? $this->mediaDataFacade->getImageTypeEntity()
                    : $this->mediaDataFacade->getVideoTypeEntity();

                $mediumDataEntity = new MediumDataEntity($campaignEntity, $mediumEntity);
                $mediumDataEntity
                    ->setCategory($campaignEntity->getId())
                    ->setPosition($maxPosition++);

                $this->mediaDataFacade->saveFileUpload($mediumDataEntity, $file);

                $this->campaignFacade->getEntityManager()->persist($mediumDataEntity)->persist($mediumEntity);
                $supported[] = $file->getName();
            }


            $this->campaignFacade->getEntityManager()->persist($campaignEntity)->flush();

            $translator = $this->translateMessage();

            $title = $translator->translate('campaignPage.management');
            if ($unsupported) {
                $message = $translator->translate('campaignPage.medium.unsupported', count($unsupported), ['media' => implode(", ", $unsupported)]);
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            }
            if ($supported) {
                $message = $translator->translate('campaignPage.medium.supported', count($supported), ['media' => implode("`, `", $supported)]);
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);
            }

            $message    = $translator->translate('campaignPage.campaign_updated', null, ['name' => $campaignEntity->getName()]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);

            $this->ajaxRedirect('this', null, ['flash', 'media']);
        };

        return $form;
    }


    public function handleAddCampaign()
    {
        $this->campaign = null;
        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['campaignFormModal']);
    }


    /**
     * sorting media position
     *
     * @param $item_id
     * @param $prev_id
     * @param $next_id
     * @throws \Exception
     */
    public function handleSort($item_id, $prev_id, $next_id)
    {
        $assocMediaEntity = [];
        $assocPosition = [];
        $assocIndex = [];

        /** @var MediumDataEntity[] $mediaEntity */
        $mediaEntity = $this->mediaDataFacade->getRepository()->findBy(['campaign' => $this->campaign], ['position' => 'ASC']);

        /*
         * sort by id
         */
        foreach ($mediaEntity as $index => $item) {
            $assocIndex [$item->getId()] = $index;
            $assocPosition [$item->getId()] = $item->position;
            $assocMediaEntity[$item->getId()] = $item;
        }

        /*
         * sort by position
         */
        usort( $mediaEntity, function ($a, $b) {
            if ($a->position == $b->position) return 0;
            return ($a->position < $b->position) ? -1 : 1;
        });


        /** @var MediumDataEntity $itemEntity */
        if ($itemEntity = $this->mediaDataFacade->getRepository()->find($item_id)) {

            if ($prev_id == null) {
                $position = 0;
                $itemEntity->setPosition($position);

            } else {
                $position = $assocPosition[$prev_id] + 1;
                $itemEntity->setPosition($position);

            }
            if ($next_id == null) {
                $last  = end($assocPosition);
            }

            $this->mediaDataFacade->getRepository()->getEntityManager()->persist($itemEntity)->flush();
        }


        $this->ajaxRedirect('this', null, ['flash', '_media']);
    }


    public function handleDeleteCampaign($id)
    {


    }


    public function removeMedia($ids)
    {
        $notRemoved = [];
        $removed = [];

        foreach ($ids as $id) {
            if ($this->removeMedium($id)) {
                $removed[] = $id;

            } else {
                $notRemoved[] = $id;
            }
        }

        $translator = $this->translateMessage();

        if ($notRemoved) {
            $message    = $translator->translate('campaignPage.medium.not_found', null, ['name' => implode(', ', $notRemoved)]);
            $title      = $translator->translate('campaignPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
        }
        if ($removed) {
            $message    = $translator->translate('campaignPage.medium.removed', null, ['name' => implode(', ', $removed)]);
            $title      = $translator->translate('campaignPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);
            $this->mediaDataFacade->getRepository()->getEntityManager()->flush();
        }

        $this->ajaxRedirect('this', null, ['flash', 'media']);
    }

    public function removeMedium($id)
    {
        /** @var MediumDataEntity $mediumEntity */
        if (!$mediumEntity = $this->mediaDataFacade->getRepository()->find($id)) {
            return false;

        } else {
            $this->mediaDataFacade->removeFileFromMedium($mediumEntity);
            $this->mediaDataFacade->getRepository()->getEntityManager()->remove($mediumEntity);
            return true;
        }
    }


    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDeleteMedium($id)
    {
        $translator = $this->translateMessage();

        if (!$this->removeMedium($id)) {
            $message    = $translator->translate('campaignPage.medium.not_found', null, ['name' => $id]);
            $title      = $translator->translate('campaignPage.management');

        } else {
            $message    = $translator->translate('campaignPage.medium.removed', null, ['name' => $id]);
            $title      = $translator->translate('campaignPage.management');

            $this->mediaDataFacade->getRepository()->getEntityManager()->flush();
        }

        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_CAMPAIGN_EDIT_SUCCESS);
        $this->ajaxRedirect('this', null, ['flash', 'media']);
    }


    public function handleEditCampaign($id)
    {

        $this->campaign = $id;
//        $this->payload->url = $this->link('this');
        $this->ajaxRedirect('this', null, ['campaignFormModal']);
    }







    /**
     * @return DeviceEntity[]|null
     */
    public function getDevices()
    {
        if (null === $this->devices) {
            $this->devices = $this->deviceRepository->getAssoc($this->deviceRepository->fetch($this->deviceRepository->getUserAllowedQuery($this->user))->getIterator());
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