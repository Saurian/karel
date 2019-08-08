<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    SettingsPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Facades\CampaignFacade;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\UserFacade;
use CmsModule\Forms\IUserFormFactory;
use CmsModule\Repositories\LogRepository;
use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\DateTime;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;

class SettingsPresenter extends BasePresenter
{


    /** @var LogRepository @inject */
    public $logRepository;

    /** @var EntityManager @inject */
    public $em;

    /** @var UserFacade @inject */
    public $userFacade;

    /** @var CampaignFacade @inject */
    public $campaignFacade;

    /** @var DeviceFacade @inject */
    public $deviceFacade;



    /** @var EntityFormMapper @inject */
    public $entityFormMapper;



    /** @var IUserFormFactory @inject */
    public $userFormFactory;



    public function handleDeleteLog($id)
    {
        if (!$entity= $this->logRepository->find($id)) {
            $this->flashMessage('Log nenalezen', 'danger');
            $this->ajaxRedirect();
        }

        $this->em->remove($entity)->flush();
        $this->flashMessage("Log {$entity} smazán", 'success');
        $this->ajaxRedirect();
    }


    public function handleDeleteCampaign($id)
    {
        /** @var CampaignEntity $entity */
        if (!$entity = $this->campaignFacade->getRepository()->find($id)) {
            $this->flashMessage('Kampaň nenalezena', 'danger');
            $this->ajaxRedirect();
        }

        $this->campaignFacade->removeMediaFromCampaign($entity);
        $this->em->remove($entity)->flush();

        $message = "Kampaň `{$entity}` smazána";
        $title      = $this->translator->translate('campaignPage.management');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect();
    }


    public function handleDeleteDevice($id)
    {
        /** @var DeviceEntity $entity */
        if (!$entity = $this->deviceFacade->getRepository()->find($id)) {
            $this->flashMessage('Zařízení nenalezeno', 'danger');
            $this->ajaxRedirect();
        }

        $this->em->remove($entity)->flush();

        $message = "Zařízení `{$entity->getName()}` smazáno";
        $title      = $this->translator->translate('campaignPage.management');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect();
    }


    public function handleDeleteDeviceGroup($id)
    {
        /** @var DeviceGroupEntity $entity */
        if (!$entity = $this->deviceFacade->getDeviceGroupRepository()->find($id)) {
            $this->flashMessage('Skupina zařízení nenalezena', 'danger');
            $this->ajaxRedirect();
        }

        $this->em->remove($entity)->flush();

        $message = "Skupina zařízení `{$entity->getName()}` smazána";
        $title      = $this->translator->translate('campaignPage.management');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect();
    }


    public function handleDeleteUser($id)
    {
        if (!$entity= $this->userRepository->find($id)) {
            $this->flashMessage('Uživatel nenalezen', 'danger');
            $this->ajaxRedirect();
        }

        $this->em->remove($entity)->flush();
        $this->flashMessage("Uživatel {$entity->username} smazán", 'success');
        $this->ajaxRedirect();
    }


    public function userUserColumnEdited($id, $key)
    {
        if (!$entity= $this->userRepository->find($id)) {
            $this->flashMessage('Uživatel nenalezen', 'danger');
            $this->ajaxRedirect();
        }

        $value = $this->getParameter('value');
        if (isset($entity->$key)) {
            $entity->$key = $value;
            $this->em->persist($entity)->flush();
            $this->flashMessage("Uživatel {$entity->username} upraven", 'success');
        }

        $this->ajaxRedirect();
    }


    public function renderDefault()
    {



    }



    protected function createComponentLogsGridControl()
    {
        $grid = new DataGrid();
        $data = $this->logRepository->createQueryBuilder('a');
        $grid->setDataSource($data);
        $grid->setItemsPerPageList([20, 30, 50, 100]);

        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent();

        $grid->addColumnText('targetKey', 'EntityID')
            ->setSortable()
            ->setFitContent()
            ->setAlign('center')
            ->setFilterText();


        $grid->addColumnDateTime('inserted', 'Vložen')
            ->setSortable();
//            ->setFilterDateRange();


        $grid->addColumnText('role', 'Role')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('type', 'Typ')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('action', 'Akce')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('message', 'Zpráva')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('level', 'Úroveň')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();


        $grid->addAction('delete', 'Smazat', 'deleteUser!')
            ->setIcon('trash fa-2x')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat šablonu [id: {$item->id}]?";
            });


        $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/log_grid_detail.latte');
        $grid->setTranslator($this->translator);
        return $grid;
    }


    protected function createComponentUsersGridControl()
    {
        $grid = new DataGrid();
        $data = $this->userRepository->createQueryBuilder('a');
        $grid->setDataSource($data);

        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setFitContent();


        $grid->addColumnDateTime('inserted', 'Vložen')
            ->setSortable();
//            ->setFilterDateRange();


        $grid->addColumnStatus('active', 'Aktivní')
            ->setSortable()
            ->setFilterText();

//        $grid->addColumnStatus('role', 'Role')
//            ->setSortable()
//            ->setFilterSelect(['watcher', 'editor', 'master', 'admin']);

        $grid->addColumnStatus('role', 'Role')
            ->setCaret(true)
            ->addOption('watcher', 'pozorovatel')
            ->setClass('btn-success btn-block')
            ->endOption()
            ->addOption('editor', 'editor')
            ->setClass('btn-primary btn-block')
            ->endOption()
            ->addOption('master', 'mistr')
            ->setClass('btn-info btn-block')
            ->endOption()
            ->addOption('admin', 'správce')
            ->setClass('btn-danger btn-block')
            ->endOption()
            ->setSortable()
            ->onChange[] = function ($id, $value) {
                if ($entity= $this->userRepository->find($id)) {
                    $entity->role = $value;
                    $this->em->persist($entity)->flush();
                    $this->flashMessage("Uživatel {$entity->username} upraven", 'success');
//                    $this->ajaxRedirect('this', null, 'flash');
                    $this['usersGridControl']->redrawItem($id);
                    $this->ajaxRedirect('this', null, 'flash');
                }

//                $this['mediaGridControl']->redrawItem($id);
//                $this['templatesGridControl']->reload();
            };

        $grid->getColumn('role')->setFilterSelect(['' => 'všechny', 'watcher' => 'pozorovatel', 'editor' => 'editor', 'master' => 'mistr', 'admin' => 'správce'])->addAttribute('class', 'btn-block btn');


        $grid->addColumnText('firstName', 'Jméno')
            ->setSortable()
//            ->setEditableCallback([$this, 'userColumnEdited'])
            ->setEditableCallback(function ($id, $value) {
                if (Validators::is($value, 'string:3..32')) {
                    if ($entity= $this->userRepository->find($id)) {
                        $entity->firstName = $value;
                        $this->em->persist($entity)->flush();
                        $this->flashMessage("Uživatel {$entity->username} upraven", 'success');
                        $this['usersGridControl']->redrawItem($id);
                        $this->ajaxRedirect('this', null, 'flash');
                    }
                }
            })
            ->setFilterText();

        $grid->addColumnText('lastName', 'Příjmení')
            ->setSortable()
            ->setEditableCallback(function ($id, $value) {
                if (Validators::is($value, 'string:3..32')) {
                    if ($entity= $this->userRepository->find($id)) {
                        $entity->lastName = $value;
                        $this->em->persist($entity)->flush();
                        $this->flashMessage("Uživatel {$entity->username} upraven", 'success');
                        $this->ajaxRedirect('this', null, 'flash');
                    }
                }
            })
            ->setFilterText();

        $grid->addColumnText('username', 'Login')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('mail', 'e-mail')
            ->setSortable()
            ->setFilterText();






        $grid->addAction('delete', 'Smazat', 'deleteLog!')
            ->setIcon('trash fa-2x')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat uživatele [username: {$item->username}]?";
            });


        $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/user_grid_detail.latte');
        $grid->setItemsDetailForm(function (Container $container) use ($grid) {

            $form = $this->userFormFactory->create();

            $postItemsDetailForm = $this->request->getPost('items_detail_form');
            $submitted = isset($postItemsDetailForm[$container->getName()]);


            Debugger::barDump($postItemsDetailForm);
            Debugger::barDump($submitted);


            Debugger::barDump($_GET, 'DetailForm');
            Debugger::barDump($this->getParameters(), 'DetailForm');
            Debugger::barDump($_REQUEST, 'request');
            Debugger::barDump($this->getHttpRequest(), 'httpRequest');
            Debugger::barDump($this->getRequest(), 'request');
            Debugger::barDump($container, 'container');
            Debugger::barDump($grid->getParameterId('id'), 'DetailForm');



//            $id = $this->getRequest()->getParameter($grid->getParameterId('id'));
            $id = $container->getName();
            Debugger::barDump($id, 'DetailForm');



            $name = 'userDetailForm';
            $form->setTranslator($this->translator->domain('messages.forms.' . $name));
            $form->setFormName($name);
            $form->setId($id);

            $form->create($container);
            $form->bootstrap3Render();

            $entity = $this->userRepository->find($id);
            Debugger::barDump($entity);
//            Debugger::barDump($form->getComponents());

//            $firstName = $form->getComponents()['firstName'];
//            Debugger::barDump($firstName);
//            $container->addComponent($firstName, 'first');


//            $form->bindEntity($entity);
            if (!$submitted) {
                $this->entityFormMapper->load($entity, $container);

            }

//            $container->addText('firstName', 'První svého');


            foreach ($form->getComponents() as $component) {
//                $container->addComponent($component, $component->name);
            }
//            $container->addComponent($form->getComponent('firstName'), 'firstName');


            Debugger::barDump($container, 'container before');




//            $container->addHidden('id');
//            $container->addText('firstName', 'Jméno');
//            $container->addText('lastName', 'Příjmení');
//            $container->addCheckboxList('devices', 'Zařízení', ['asd', 'sdwdd', 'defef']);
//            $container->addCheckboxList('devicesGroups', 'Skupiny zařízení');



            Debugger::barDump($container->getErrors(), 'Errors');


            $container->addSubmit('save', 'Save')
                ->onClick[] = function(SubmitButton $button) use ($entity, $container)  {

                if ($container->isValid()) {
//                    $this->entityFormMapper->save($entity, $container);
                }


                $values = $button->getParent()->getValues();

                Debugger::barDump($container, 'container after');
                dump($values);
                dump($container->isValid());
//                dump($entity);

                Debugger::barDump($button);

//                dump($button);
                die();



                $values = $button->getParent()->getValues();
//                $presenter['examplesGrid']->redrawItem($values->id);
            };

        });




//        $grid->setItemsDetail(__DIR__ . '/templates/DataGrid/log_grid_detail.latte');
        $grid->setTranslator($this->translator);
        return $grid;
    }


    protected function createComponentCampaignsGridControl($name)
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);
        $repository = $this->campaignFacade->getRepository();

        $campaignsQuery = $repository->getQuery();
        if (!$this->user->isAllowed('Cms:Campaign', 'listAllCampaigns')) {
            $campaignsQuery->byUser($this->user);
        }

        $qb = $campaignsQuery->doCreateQueryBuilder($this->userRepository);

        $grid->setDataSource($qb);

        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent();

        $grid->addColumnDateTime('inserted', 'Vloženo')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent()
            ->setFilterDateRange();

        $grid->addColumnNumber('name', 'Název kampaně')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnDateTime('realizedFrom', 'Realizace od')
            ->setFormat('Y-m-d H:i');

        $grid->addColumnDateTime('realizedTo', 'Realizace do')
            ->setFormat('Y-m-d H:i');

        $grid->addAction('delete', 'Smazat', 'deleteCampaign!')
            ->setIcon('trash fa-2x')
            ->setClass('_ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat kampaň [id:{$item->id}  name:{$item->name}]?";
            });

        return $grid;
    }


    protected function createComponentDevicesGridControl($name)
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);
        $query = $this->deviceFacade->getDeviceRepository()->getUserAllowedQuery($this->user);

        $qb = $query->doCreateQueryBuilder($this->deviceFacade->getRepository());

        $grid->setDataSource($qb);

        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent();

        $grid->addColumnDateTime('inserted', 'Vloženo')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent()
            ->setFilterDateRange();

        $grid->addColumnText('sn', 'Sn')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('name', 'Název kampaně')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('version', 'Verze')
            ->setSortable()
            ->setFitContent()
            ->setFilterText();


        $grid->addAction('delete', 'Smazat', 'deleteDevice!')
            ->setIcon('trash fa-2x')
            ->setClass('_ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat zařízení [id:{$item->id}  name:{$item->name} sn:{$item->sn}]?";
            });

        return $grid;
    }


    protected function createComponentDevicesGroupsGridControl($name)
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);
        $qb = $this->em->getRepository(DeviceGroupEntity::class)->createQueryBuilder('e');

        $repository = $this->deviceFacade->getDeviceGroupRepository();

        $qb = $repository->getUserAllowedQuery($this->user)->doCreateQueryBuilder($repository);


        $grid->setDataSource($qb);

        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent();

        $grid->addColumnDateTime('inserted', 'Vloženo')
            ->setSortable()
            ->setAlign('center')
            ->setFitContent()
            ->setFilterDateRange();


        $grid->addColumnText('name', 'Název kampaně')
            ->setSortable()
            ->setFilterText();



        $grid->addAction('delete', 'Smazat', 'deleteDeviceGroup!')
            ->setIcon('trash fa-2x')
            ->setClass('_ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat skupinu zařízení [id:{$item->id}  name:{$item->name}]?";
            });

        return $grid;
    }


    /**
     * @param DeviceEntity[] $devices
     */
    public function getCheckboxDeviceList($devices)
    {
        $result = [];
        foreach ($devices as $device) {
            $result[$device->getId()] = "{$device->sn}: {$device->getName()}";
        }

        return $result;
    }


}