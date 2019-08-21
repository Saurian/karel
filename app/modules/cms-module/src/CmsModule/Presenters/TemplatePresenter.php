<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    TemplatePresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\MediumEntity;
use CmsModule\Entities\TemplateEntity;
use CmsModule\Forms\IAdminTemplateFormFactory;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\MediaRepository;
use CmsModule\Repositories\TemplateRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\Container;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

class TemplatePresenter extends BasePresenter
{

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var TemplateRepository @inject */
    public $templateRepository;

    /** @var MediaRepository @inject */
    public $mediaRepository;


    /** @var EntityManager @inject */
    public $em;

    /** @var IAdminTemplateFormFactory @inject */
    public $adminTemplateFormFactory;

    /** @var TemplateEntity */
    private $templateEntity;

    /** @persistent */
    public $editTemplateId;


    protected $enableAjaxLayout = false;


    public function renderDefault()
    {
/*
        $translator = $this->translateMessage();
        $message      = $translator->translate('template.media_type_not_found');
        $title        = $translator->translate('template.management');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
*/

    }


    public function handleDelete($id)
    {
        $translator = $this->translateMessage();
        if (!$templateEntity = $this->templateRepository->find($id)) {
            $message      = $translator->translate('template.template_not_found');
            $title        = $translator->translate('template.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect('default');
        }

        $this->em->remove($templateEntity)->flush();
        $message = $translator->translate('template.template_deleted');
        $title   = $translator->translate('template.management');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        $this->ajaxRedirect('default', 'hoursGrid');

        $this['templatesGridControl']->reload();
    }


    public function handleDeleteMedium($id, $mid)
    {
        $translator = $this->translateMessage();
        if (!$entity = $this->em->getRepository(MediumEntity::class)->find($mid)) {
            $message      = $translator->translate('template.media_type_not_found');
            $title        = $translator->translate('template.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect();
        }

        $this->em->remove($entity)->flush();
        $message = $translator->translate('template.medium_deleted');
        $title   = $translator->translate('template.management');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);

        $this['mediaGridControl']->reload();
        $this['templatesGridControl']->reload();
        $this->ajaxRedirect('this', null, 'formModal');
    }


    public function handleEdit($id)
    {
        $translator = $this->translateMessage();
        if (!$entity = $this->templateRepository->find($id)) {
            $message      = $translator->translate('template.template_not_found');
            $title        = $translator->translate('template.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect('default');
        }

        $this->editTemplateId = $id;
        $this->payload->_edit = $id;
        $this->template->entity = $entity;
        $this->template->rnd = rand(0,2000);

        $this->ajaxRedirect();
//        $this->ajaxRedirect('this', null, ['formModal']);
//        $this->redrawControl();

//        $this['mediaGridControl']->redrawControl();
//        $this->redrawControl('formModal');
    }


    public function handleSort()
    {

    }


    public function actionEdit($id)
    {
        if (!$templateEntity = $this->templateRepository->find($id)) {
            $templateEntity = new TemplateEntity();
        }

        $this->templateEntity = $templateEntity;
    }


    private function getEditableTemplate()
    {
        static $templateEntity = null;

        $id = $this->getParameter('id')
            ? $this->getParameter('id')
            : $this->editTemplateId;

        if (!$templateEntity && $id) {
            $templateEntity = $this->templateRepository->find($id);
        }

        return $templateEntity;
    }


    protected function createComponentMediaGridControl($name)
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);
        $messages = $this->translateMessage();
//        $grid->setRefreshUrl(false);
//        $grid->setRememberState(false);

        $templateEntity = $this->getEditableTemplate();

        $data = $this->templateRepository->createQueryBuilder()
            ->from(MediumEntity::class, 'a')
            ->select('a')
            ->addSelect('t')
            ->leftJoin('a.template', 't');

        if ($templateEntity) {
            $data->where('t = :template')->setParameter('template', $templateEntity);
        }


//        $grid->setSortable();
        $grid->setDataSource($data);

        $grid->addColumnStatus('type', 'messages.template.mediaGrid.type')
            ->setCaret(true)
            ->addOption('image', 'messages.mediaType.image')
            ->setIcon('file-image-o')
            ->setClass('btn-success btn-block')
            ->endOption()
            ->addOption('zip', 'messages.mediaType.zip')
            ->setIcon('file-archive-o')
            ->setClass('btn-primary btn-block')
            ->endOption()
            ->addOption('video', 'messages.mediaType.video')
            ->setIcon('file-movie-o')
            ->setClass('btn-info btn-block')
            ->endOption()
            ->addOption('url', 'messages.mediaType.url')
            ->setIcon('file-text-o')
            ->setClass('btn-danger btn-block')
            ->endOption()
            ->onChange[] = function ($mediumId, $type) {
            $entity = $this->em->getRepository(MediumEntity::class)->find($mediumId);
            $entity->type = $type;

            $this->em->persist($entity)->flush();
            $this['mediaGridControl']->redrawItem($mediumId);
            $this['templatesGridControl']->reload();
        };

        $grid->addColumnText('name', 'messages.template.mediaGrid.name', 'template.name')
            ->setRenderer(function (MediumEntity $mediumEntity) use ($messages) {

                $result = Html::el('div');

                if ($mediumEntity->getType() == 'image') {
                    $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-image-o')->setText($messages->translate('template.mediaGrid.image'));
                    $result->addHtml($el);

                } elseif ($mediumEntity->getType() == 'zip') {
                    $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-archive-o')->setText($messages->translate('template.mediaGrid.zip'));
                    $result->addHtml($el);

                } elseif ($mediumEntity->getType() == 'url') {
                    $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-text-o')->setText($messages->translate('template.mediaGrid.url'));
                    $result->addHtml($el);

                } elseif ($mediumEntity->getType() == 'video') {
                    $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-movie-o')->setText($messages->translate('template.mediaGrid.video'));
                    $result->addHtml($el);
                }

                return $result;
            });


        $grid->addAction('delete', 'messages.grids.action.delete', 'deleteMedium!', ['id' => 'template.id', 'mid' => 'id'])
            ->setIcon('trash fa-2x')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) use ($messages) {
                return $messages->translate('template.mediaGrid.delete_confirmation', null, ['d' => $item->id]);
            });


        $grid->addInlineAdd()
            ->setPositionTop()
            ->onControlAdd[] = function (Container $container) use ($templateEntity) {
                $container->addText('templateId', '')->setAttribute('readonly')->setValue($this->editTemplateId);
                $container->addSelect('type', '', ['image' => 'messages.mediaType.image', 'video' => 'messages.mediaType.video', 'url' => 'messages.mediaType.url', 'zip' => 'messages.mediaType.zip']);
            };

        $p = $this;

        $grid->getInlineAdd()->onSubmit[] = function($values) use ($p) {

            /**
             * Save new values
             */
            $templateId = $values->templateId;

            /** @var TemplateEntity $templateEntity */
            if ($templateEntity = $this->templateRepository->find($templateId)) {
                $entity = (new MediumEntity('image'))->setTemplate($templateEntity);

                foreach ($values as $key => $value) {
                    if (isset($entity->$key)) {
                        $entity->$key = $value;
                    }
                }

                $this->em->persist($entity)->flush();

                $v='';foreach($values as $key=>$value){$v.="$key: $value, ";}$v=trim($v,', ');

                $message = "Record with values [$v] was added!";
                $p->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa medií', FlashMessageControl::TOAST_SUCCESS);

                $this['mediaGridControl']->reload();
                $this['templatesGridControl']->reload();

                $this->editTemplateId = $templateId;
//                $this->ajaxRedirect('this', null, 'formModal');
            }

        };

        return $grid;
    }


    protected function createComponentTemplatesGridControl($name)
    {
        $grid = new DataGrid();
//        $grid->setSortable();
        $messages = $this->translateMessage();

        $data = $this->templateRepository->createQueryBuilder('a')
            ->addSelect('m')
            ->addSelect('c')
            ->addSelect('u')
            ->leftJoin('a.media', 'm')
            ->leftJoin('a.campaigns', 'c')
            ->join('a.createdBy', 'u');

        $query = $this->templateRepository->getUserAllowedQuery($this->user)->getQueryBuilder($this->templateRepository);

//        $query
//            ->addSelect('m')
//            ->addSelect('c')
//            ->addSelect('u')
//            ->leftJoin('q.media', 'm')
//            ->leftJoin('q.campaigns', 'c')
//            ->join('q.createdBy', 'u');


//        dump($query);



        $grid->setDataSource($query);

        $grid->addColumnNumber('id', 'messages.template.templateGrid.id')
            ->setSortable()
            ->setFitContent();


        $grid->addColumnDateTime('inserted', 'messages.template.templateGrid.inserted')
            ->setSortable();
//            ->setFilterDateRange()

        $grid->addColumnText('name', 'messages.template.templateGrid.name')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('createdBy', 'messages.template.templateGrid.createdBy')
            ->setSortableCallback(function (\Kdyby\Doctrine\QueryBuilder $queryBuilder, $sort) {
                $queryBuilder->addOrderBy('u.firstName', $sort['createdBy'])->addOrderBy('u.lastName', $sort['createdBy']);

            })
            ->setSortable()
            ->setRenderer(function (TemplateEntity $item) {
                return $item->getCreatedBy()->getFullName();
            })
            ->setFilterText();

        $grid->addColumnText('media', 'messages.template.templateGrid.media')
            ->setRenderer(function (TemplateEntity $item) use ($grid, $name) {
                if (!$item->getMedia()->isEmpty()) {
                    $result = Html::el('div');

                    foreach ($item->getMedia() as $mediumEntity) {
                        if ($mediumEntity->getType() == 'image') {
                            $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-image-o');
                            $result->addHtml($el);

                        } elseif ($mediumEntity->getType() == 'zip') {
                            $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-archive-o');
                            $result->addHtml($el);

                        } elseif ($mediumEntity->getType() == 'url') {
                            $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-text-o');
                            $result->addHtml($el);

                        } elseif ($mediumEntity->getType() == 'video') {
                            $el = Html::el('span')->setAttribute('class', 'm-2 fa fa-file-movie-o');
                            $result->addHtml($el);
                        }

                    }

                    return $result;
                }

                return null;
            });
//            ->setFilterMultiSelect(['image', 'zip', 'video', 'url'], 'name');

        $grid->addColumnText('campaigns', 'messages.template.templateGrid.campaigns')
            ->setRenderer(function (TemplateEntity $item) use ($grid, $name) {

//                dump($item->camp);

                $html = Html::el('span');


                if ($item->getCampaigns()->isEmpty()) {
                    $html->setAttribute('class', 'fa fa-battery-empty');

                } else {
                    $html->setAttribute('class', 'fa fa-battery-full');
                    $html->setText(' ' . $item->getCampaigns()->count() . "x");

                }

                return $html;
            });



/*
        $grid->addColumnLink('detail', "Detail")
            ->setRenderer(function (TemplateEntity $item) use ($grid, $name) {
                $link = Html::el('a')->href($grid->link('getItemDetail!', ['id' => $item->getId()]));

                $link
                    ->setAttribute('data-toggle-detail', $item->getId())
                    ->setAttribute('data-toggle-detail-grid', 'deviceGrid')
                    ->setAttribute('data-ajax-off', 'datargid.item_detail')
                    ->setAttribute('class', 'ajax item-detail-link');

                return $link;
            })
            ->setFilterText();
*/

//        $grid->setTemplateFile(__DIR__ . '/templates/Device/datagrid_device.latte');
//        $grid->setItemsDetail();
//        $grid->setItemsDetail(__DIR__ . '/templates/Device/grid_item_detail.latte');

        $presenter = $this;



/*
        $grid->addAction('edit', 'Edit')
            ->setIcon('pencil')
            ->setClass('ajax-modal btn btn-xs btn-default')
            ->setDataAttribute('ajax', 'false')
            ->setDataAttribute('popup-type', 'modal-md')
            ->setDataAttribute('popup-title', 'Přidat ticket')
            ->setDataAttribute('popup-dialog', 'popup');
*/


        $grid->addAction('delete', 'messages.grids.action.delete', 'delete!')
            ->setIcon('trash fa-2x')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) use ($messages) {
                return $messages->translate('template.templateGrid.delete_confirmation', null, ['d' => $item->id]);
            });


        $grid->allowRowsAction('delete', function(TemplateEntity $item) {
            return $item->getCampaigns()->isEmpty();
        });


        $grid->addAction('edit', 'messages.grids.action.edit', 'edit!')
            ->setIcon('edit fa-2x')
            ->setClass('ajax btn btn-xs btn-default');

        $grid->allowRowsAction('edit', function(TemplateEntity $item) {
            return $item->getCampaigns()->isEmpty();
        });



        $grid->addInlineAdd()
            ->setPositionTop()
            ->onControlAdd[] = function (Container $container) {

//            $container->addText('id', '')->setAttribute('readonly');
            $container->addText('name', '');
        };

        $p = $this;

        $grid->getInlineAdd()->onSubmit[] = function($values) use ($p) {

            /**
             * Save new values
             */
            $entity = new TemplateEntity();
            foreach ($values as $key => $value) {
                $entity->$key = $value;
            }

            $entity->updateUser($this->userEntity);
            $this->em->persist($entity)->flush();

            $v='';foreach($values as $key=>$value){$v.="$key: $value, ";}$v=trim($v,', ');

            $message = "Record with values [$v] was added! (not really)";

//            $p->flashMessage();

            $p->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa kampaní', FlashMessageControl::TOAST_SUCCESS);

            $p->redrawControl('flashes');

            $this->redrawControl('flashes');
            $this['templatesGridControl']->redrawControl();
        };



        $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/template_grid_detail.latte');
        $grid->setItemsDetailForm(function (Container $container) {

            $container->addHidden('id');
            $container->addText('name');

            $container->addSubmit('save', 'Save')
                ->onClick[] = function($button)  {
//                $values = $button->getParent()->getValues();
//                $presenter['examplesGrid']->redrawItem($values->id);
            };

        });


        $grid->addInlineEdit()
            ->onControlAdd[] = function($container) {
            $container->addText('name', '');
        };

        $grid->getInlineEdit()->onSetDefaults[] = function($container, $item) {
            $container->setDefaults([
                'id' => $item->id,
                'name' => $item->name,
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function($id, $values) {

            if (!$templateEntity = $this->templateRepository->find($id)) {
                $this->flashMessage('Šablona nenalezena', 'danger');
                $this->ajaxRedirect('default');
                return;
            }

            foreach ($values as $key => $value) {
                $templateEntity->$key = $value;
            }

            $this->em->persist($templateEntity)->flush();
            $this->flashMessage('Šablona upravena', 'success');
            $this->ajaxRedirect();
        };


        $grid->setTranslator($this->translator);

        return $grid;
    }



    protected function createComponentAdminTemplateControl()
    {
        $form = $this->adminTemplateFormFactory->create();


        $entity = $this->templateEntity;

        $form->create();
        $form->bootstrap3Render();

        $form->bindEntity($entity);



        return $form;

    }





}