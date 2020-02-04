<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UsersPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\UserFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\IUserFormFactory;
use CmsModule\Forms\UserForm;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use Devrun\CmsModule\Controls\DataGrid;
use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Nette;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Controls\SubmitButton;
use Tracy\Debugger;
use Ublaboo\DataGrid\Utils\ItemDetailForm;

class UsersPresenter extends BasePresenter
{

    /** @var IUserFormFactory @inject */
    public $userFormFactory;

    /** @var UserFacade @inject */
    public $userFacade;

    /** @var DeviceFacade @inject */
    public $deviceFacade;

    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    /** @var UserEntity[] */
    private $rows = [];

    private $newPassword;

    /** @var integer @persistent */
    public $editUser;

    /** @var UserEntity */
    private $entity;


    /** @var EntityFormMapper @inject */
    public $entityFormMapper;


    public function handleItemsNested($nestedData, $elementId)
    {
        $nestedData = json_decode($nestedData);

        $sortData = array_map(function ($data) {
            return $data->id;
        }, $nestedData);

        $positionedData = array_flip($sortData);

        $rows = $this->getRows();
        $entity = $rows[$elementId];
        $indexRows = array_values($rows);
        $targetRow = $indexRows[$positionedData[$elementId]];

        $targetPosition = $targetRow->position;
        $em = $this->userFacade->getEntityManager();

        $entity->position = $targetPosition;
        $em->persist($entity)->flush();

        $this->payload->_nested_success = true;
        $this->ajaxRedirect('this', null, ['items', 'flash']);
    }



    public function handleDetail($id)
    {
        $this->template->toggle_detail = $id;
        $this->payload->_toggle_detail = $id;

        $this->ajaxRedirect('this', null, ['items']);
    }


    public function handleSetFilter($active)
    {
        $this->userRepository->setFilterActive($active);
        $filter = $this->userRepository->getFilterActive();

        $message = "Nastaven filtr";
        if ($filter === "1") {
            $message = "Nastaven filtr `Správa aktivních uživatelů`";

        } elseif ($filter === "0") {
            $message = "Nastaven filtr `Správa neaktivních uživatelů`";

        } elseif ($filter === null) {
            $message = "Nastaven filtr `Správa všech uživatelů`";
        }

        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatelů', FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect('this', null, ['users', 'flash']);
    }


    public function handleToggleActive($uid, $checked)
    {
        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        /** @var UserEntity $element */
        if ($element = $this->userRepository->find($uid)) {

            $this->userFacade->setActive($element, $checked);

            $message = "Uživatel `{$element->getUsername()}` je nyní " . ($element->isActive() ? 'aktivní' : 'neaktivní');
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa uživatele', FlashMessageControl::TOAST_SUCCESS);
        }

        if ($this->userRepository->existFilterActive()) {
            if (($filterActive = $this->userRepository->getFilterActive()) !== null) {
                $this->payload->_switchery_redraw = true;
                $this->payload->_filter_toggle = true;
                $this->ajaxRedirect('this', null, ['users', 'filter', 'flash']);
                return;
            }
        }

        $this->ajaxRedirect('this', null, ['filter', 'flash']);
    }


    public function handleDelete($id)
    {
        $translator = $this->translateMessage();

        /** @var UserEntity $entity */
        if (!$entity = $this->userRepository->find($id)) {
            $title = $translator->translate('campaignPage.management');
            $message = $translator->translate('campaignPage.campaign_not_found', null, ['id' => $id]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect('this', null, 'flash');

        } else {
            $this->userRepository->getEntityManager()->remove($entity)->flush();
            $title = $translator->translate('userPage.management');
            $message = $translator->translate('userPage.userRemoved', null, ['name' => $entity->getFullName()]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);

            $this->payload->url = $this->link('this');
            $this->ajaxRedirect('this', 'usersGridControl', 'flash');
        }

    }

    public function handleEdit($id)
    {
        $translator = $this->translateMessage();
        $this->editUser = $id;

        if ($id) {
            if (!$entity = $this->getEntity()) {
                $title   = $translator->translate('campaignPage.management');
                $message = $translator->translate('campaignPage.campaign_not_found', null, ['id' => $id]);
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
                $this->ajaxRedirect('this', null, 'flash');
            }

//            $this->template->userEntity = $entity;
        }

//        $this->template->editUser = $id;
//        $this->payload->url = $this->link('this');

        $this->ajaxRedirect('this', null, ['userFormModal']);
    }


    public function actionInit()
    {
        $this->userFacade->initPositions();

    }


    public function actionDefault()
    {
//        $this->template->userEntity = $this->userRepository->getEmptyUser();
    }


    public function renderDefault()
    {
//        $query = $this->getUserAllowedUsersQuery();
//        $rows  = $this->userRepository->fetch($query);
//
//        $total  = $rows->count();
//        $active = $nonActive = 0;
//
//        foreach ($rows as $row) {
//            if ($row->active) $active++;
//            if (!$row->active) $nonActive++;
//        }
//
//
//        $this->template->rows = $this->getRows();
//
//        $this->template->allUserCount       = $total;
//        $this->template->activeUserCount    = $active;
//        $this->template->nonActiveUserCount = $nonActive;


//        $this->template->userEntity = $this->entity;

        Debugger::$maxDepth = 4;

        $rootDeviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserRootDeviceGroup($this->getUser());

        $queryBuilder = $this->deviceGroupRepository->childrenQueryBuilder($rootDeviceGroupEntity);

//        dump($queryBuilder);

//        $queryBuilder->orderBy('node.lvl');

//        dump($queryBuilder->getQuery()->getResult());

//        $devicesGroups = $this->deviceGroupRepository->getCachedResult($this->deviceGroupRepository->getUserAllowedQueryBuilder($this->getUser()));
//        $devicesGroups = $this->deviceGroupRepository->getAssoc($devicesGroups);
//
//        dump($devicesGroups);

        /** @var UserEntity $entity */
        $entity = $this->userRepository->find(3);

//        dump($entity->getDevicesGroups());
        $selectedIds = [];
        foreach ($entity->getDevicesGroups() as $devicesGroup) {
            $selectedIds[] = $devicesGroup->getId();
        }



        $rawTrees = $this->deviceGroupRepository->buildTreeArray($queryBuilder->getQuery()->getArrayResult());
        $fancyTree = $this->deviceFacade->getFancyTree()->selectByIds($rawTrees, $selectedIds);



        $this->template->trees = $fancyTree;
        $this->template->debug = Debugger::dump($fancyTree, true);


//        dump($rawTrees);


        Debugger::$maxDepth = 10;
//        dump($rawTrees);
//        dump($fancyTree);


        $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                    ->select('e')
                                    ->andWhere('e.lvl = :level')->setParameter('level', 0)
                                    ->addOrderBy('e.lvl')
                                    ->addOrderBy('e.lft')
        ;

//        dump($query->getQuery()->getResult());
//        die;



    }


    /**
     * @deprecated
     *
     * display panel of users list form
     *
     * @param $name
     *
     * @return Multiplier
     */
    protected function createComponentUsersForm($name)
    {
        $self = $this;

        return new Multiplier(function ($id) use ($self, $name) {

            $entity = $self->getRows()[$id];
            $form   = new Nette\Application\UI\Form();
            $form->setTranslator($this->translator->domain("messages.forms.userDetailForm"));

            $form->addCheckbox('active', 'active')
                ->setDisabled($this->user->isAllowed(UserForm::class, 'edit') == false)
                ->setAttribute('class', 'js-switch')
                ->setAttribute('data-size', 'small');

            $form->getElementPrototype()
                ->addAttributes([
                    'class'     => 'ajax',
                    'data-name' => "CampaignForm",
                    'data-id'   => $id,
                    'data-ajax' => "false",
                ]);

            $form->setDefaults([
                'active' => $entity->isActive(),
            ]);

            // not used, there is active signal instead
            $form->onSuccess[] = function (BaseForm $form, $values) {

            };

            return $form;
        });
    }


    /**                $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                            ->select('e')
                                            ->andWhere('e.lvl = :level')->setParameter('level', 0)
                                            ->addOrderBy('e.lvl')
                                            ->addOrderBy('e.lft')
                ;

     * @deprecated
     * user detail form
     *
     * @param $name
     *
     * @return Multiplier
     */
    protected function createComponentUserDetailForm($name)
    {
        $self = $this;

        return new Multiplier(function ($index) use ($self, $name) {

            $editAllowed = $this->userEntity->getId() == $index
                ? $this->user->isAllowed(UserForm::class, 'selfEdit')
                : $this->user->isAllowed(UserForm::class, 'edit');

//            $form = new UserForm();
            /** @var UserEntity $entity */
            $entity = $self->getRows()[$index];
            $myRole = $this->user->getRoles()[0];

            $editRoles = true;
            if ($this->user->isAllowed(UserForm::class, 'editRole')) {

                $rolesDisabled = [
                    'master' => [
                        'watcher'    => [
                            'admin', 'supervisor',
                        ],
                        'editor'     => [
                            'admin', 'supervisor',
                        ],
                        'master'     => [
                            'admin', 'supervisor',
                        ],
                        'admin'      => true,
                        'supervisor' => true
                    ],

                    'admin' => [
                        'watcher'    => [
                            'supervisor',
                        ],
                        'editor'     => [
                            'supervisor',
                        ],
                        'master'     => [
                            'supervisor',
                        ],
                        'admin'      => [
                            'supervisor',
                        ],
                        'supervisor' => true
                    ]
                ];

                $editRoles = isset($rolesDisabled[$myRole][$entity->getRole()])
                    ? $rolesDisabled[$myRole][$entity->getRole()]
                    : false;
            }

//            $devices = $this->deviceFacade->getAllowedDevices($this->user);
//            $devices = $this->deviceRepository->getAllowedDevices($this->user);
            $devices = $this->deviceRepository->getAssocRows($this->deviceRepository->fetch($this->deviceRepository->getUserAllowedQuery($this->user))->getIterator());
            $devicesGroups = $this->deviceFacade->getAllowedDevicesGroups($this->user);

            /** @var UserForm $form */
            $form = $this->userFormFactory->create();

//            $form->injectEntityMapper($this->entityFormMapper);

            $form->setTranslator($this->translator->domain('messages.forms.' . $name));
            $form->setFormName($name);
            $form->setId($index);
            $form
                ->setDisAllowed($editAllowed == false)
                ->setDevices($devices)
                ->setDevicesGroups($devicesGroups)
                ->setEditRole($editRoles)
                ->setEditActive($this->user->isAllowed($this->name, 'toggleActive'))
                ->addFormClass(['ajax', 'auto-save']);

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

            $form->onSuccess[] = function (BaseForm $form, $values) use ($devices, $devicesGroups) {

//                $this->ajaxRedirect('this', null, ['items', 'flash']);
                $this->ajaxRedirect('this', null, [ 'flash']);
            };

            return $form;
        });
    }


    /**
     * new/edit user form
     *
     * @param $name
     *
     * @return \CmsModule\Forms\UserForm
     */
    protected function createComponentUserForm($name)
    {
//        $devices = $this->deviceFacade->getAllowedDevices($this->user);
//        $devices = $this->deviceRepository->getAssoc($this->deviceRepository->fetch($this->deviceRepository->getUserAllowedQuery($this->user))->getIterator());
        $devicesGroups = $this->deviceGroupRepository->getCachedResult($this->deviceGroupRepository->getUserAllowedQueryBuilder($this->getUser()));
        $devicesGroups = $this->deviceGroupRepository->getAssoc($devicesGroups);

//        dump($devices);

        $devices = $this->deviceRepository->getCachedResult($this->deviceRepository->getUserAllowedQueryBuilder($this->getUser()));
        $devices = $this->deviceRepository->getAssoc($devices);

//        dump($deviceEntities);

//        $entity = $this->template->userEntity;
        $entity = $this->getEntity();

//        if (!$entity && $this->editUser) {
//            if (!$entity = $this->userRepository->find($this->editUser)) {
//                $entity = $this->userRepository->getEmptyUser();
//            }
//        }



        $form = $this->userFormFactory->create();

        $form
            ->setDevices($devices)
            ->setDevicesGroups($devicesGroups)
            ->setFormName($name)
            ->setTranslator($this->translator)
            ->setNewPassword(Nette\Utils\Random::generate()) // $this->newPassword
            ->setEditActive($this->user->isAllowed($this->name, 'toggleActive'))
            ->setEditRole($this->getEditRoles())
            ->addFormClass(['ajax']);

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

//        dump($entity->getDevices());
//        dump($deviceList);

        $form->setDefaults([
            'devices' => $deviceList,
            'devicesGroups' => $deviceGroupList,
        ]);

        $form->onSendEmail[] = function (UserEntity $entity) {
            $title      = $this->translateMessage()->translate('userPage.management');
            $message = $this->translateMessage('messages.forms.userForm')->translate('user_has_been_send_email', $entity->mail);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        };

        $form->onResetPassword[] = function ($password, UserEntity $entity) {
            $title      = $this->translateMessage()->translate('userPage.management');
            $message = $this->translateMessage('messages.forms.userForm')->translate('user_password', null, ['password' => $password]);
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
        };

        $form->onSave[] = function ($message, UserEntity $entity) {
            $title      = $this->translateMessage()->translate('userPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->payload->_un_collapse = "#collapseUserForm";
            $this->ajaxRedirect('this', 'usersGridControl', ['flash', 'userForm']);
        };

        $form->onSaveError[] = function ($message) {
            $title      = $this->translateMessage()->translate('userPage.management');
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
        };

        $form->onSuccess[] = function (BaseForm $form, $values) {

            $this->clearEntity();
            $form->setValues([], true);
        };

        return $form;
    }


    protected function getEditRoles()
    {
        $myRole = $this->user->getRoles()[0];

        $editRoles = true;
        if ($this->user->isAllowed(UserForm::class, 'editRole')) {

            $rolesDisabled = [
                'master' => [
                    'admin', 'supervisor',
                ],

                'admin' => [
                    'supervisor',
                ]
            ];

            $editRoles = isset($rolesDisabled[$myRole])
                ? $rolesDisabled[$myRole]
                : false;
        }

        return $editRoles;
    }


    private function getUserAllowedUsersQuery()
    {
        return $this->userRepository->getUserAllowedQuery($this->user);
    }


    protected function getRows()
    {
        if (!$this->rows) {
            $query = $this->getUserAllowedUsersQuery();

            if ($this->userRepository->existFilterActive()) {
                $filterActive = $this->userRepository->getFilterActive();

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
            $this->setRows($this->userRepository->fetch($query));
        }


        return $this->rows;
    }


    private function setRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->getId()] = $row;
        }

        $this->rows = $_rows;
    }


    private function entityPairsRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->id] = $row;
        }

        return $_rows;
    }


    /**
     * inject default user newPassword
     *
     * @param $password
     */
    public function injectNewPassword($password)
    {
        $this->newPassword = $password;
    }


    /**
     * users grid
     *
     * @return DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnStatusException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentUsersGridControl()
    {
        $grid = new DataGrid();
        $grid->setTranslator($this->translator);
        $grid->setItemsPerPageList([20, 30, 50]);
        $grid->setRememberState(true);
        $grid->setRefreshUrl(false);

        $model = $this->userRepository->getUserAllowedQueryBuilder($this->getUser());

//        dump($q);



/*
        if ($this->getUser()->isAllowed('Cms:Users', 'listAllUsers')) {
            $model = $this->userRepository->createQueryBuilder('e');

        } else {
            $usersGroup = $this->userEntity->getGroup();

            $model = $this->userRepository->createQueryBuilder('e')
                                          ->addSelect('g')
                                          ->leftJoin('e.group', 'g')
                                          ->where('g = :group')->setParameter('group', $usersGroup);

        }
*/
        $grid->setDataSource($model);

        $grid->addColumnDateTime('inserted', 'Vložen')
             ->setFormat('j. n. Y H:i')
             ->setFitContent()
             ->setAlign('center')
             ->setSortable()
             ->setFilterDate()
//             ->setCondition(function (QueryBuilder $qb, $value) {
//                 $date = DateTime::createFromFormat("d. m. Y", $value)->setTime(0,0,0);
//                 $qb->andWhere('e.realizedFrom >= :realizedFrom')->setParameter('realizedFrom', $date);
//             });

        ;

        $grid->addColumnText('firstName', 'Jméno')
             ->setSortable()
             ->setFilterText();

        $grid->addColumnText('lastName', 'Příjmení')
             ->setSortable()
             ->setFilterText();

        $grid->addColumnText('username', 'Přihlašovací jméno')
             ->setSortable()
             ->setFilterText();

        $grid->addColumnText('mail', 'E-mail')
             ->setSortable()
             ->setFilterText();


        $roles = $this->userRepository->getAllRoles($this->getUser());
        $statusList = array('' => 'Vše') + $roles;

        if ($this->getUser()->isAllowed(UserForm::class, 'editRole')) {
            $translator = $this->translateMessage('messages.forms.userForm');

            $grid->addColumnStatus('role', 'Role')
                 ->setSortable()
                 ->addOption('watcher', $translator->translate('role.watcher'))
                 ->setIcon('check')
                 ->setClass('btn-default')
                 ->endOption()
                 ->addOption('editor', $translator->translate('role.editor'))
                 ->setIcon('check')
                 ->setClass('btn-success')
                 ->endOption()
                 ->addOption('master', $translator->translate('role.master'))
                 ->setIcon('check')
                 ->setClass('btn-info')
                 ->endOption()
                 ->addOption('admin', $translator->translate('role.admin'))
                 ->setClass('btn-primary')
                 ->endOption()
                 ->addOption('supervisor', $translator->translate('role.supervisor'))
                 ->setIcon('check')
                 ->setClass('btn-primary')
                 ->endOption()
                 ->setFilterSelect($statusList);

            $grid->getColumn('role')
                ->onChange[] = function ($id, $new_value) {

                /** @var UserEntity $entity */
                $entity = $this->userRepository->find($id);
                $entity->setActive($new_value);
                $this->userRepository->getEntityManager()->persist($entity)->flush();

                if ($this->isAjax()) $this['usersGridControl']->redrawItem($id); else $this->redirect('this');
            };

        } else {
            $translator = $this->translateMessage('messages.forms.userForm');

            $grid->addColumnText('role', 'Role')
                ->setSortable()
                ->setReplacement(['watcher'   => $translator->translate('role.watcher'),
                                  'editor'   => $translator->translate('role.editor'),
                                  'master'   => $translator->translate('role.master'),
                                  'admin'   => $translator->translate('role.admin'),
                                  'supervisor'   => $translator->translate('role.supervisor'),
                ])
                ->setFilterText();
        }



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

            /** @var UserEntity $entity */
            $entity = $this->userRepository->find($id);
            $entity->setActive($new_value);
            $this->userRepository->getEntityManager()->persist($entity)->flush();

            if ($this->isAjax()) $this['usersGridControl']->redrawItem($id); else $this->redirect('this');
        };






        $grid->addAction('edit', 'Upravit', 'edit!')
             ->setIcon('pencil fa-1x')
             ->setDataAttribute('toggle', 'detail-click')
             ->setDataAttribute('title', $this->translateMessage()->translate('devicePage.editDevice'))
             ->setTitle($this->translateMessage()->translate('devicePage.editDevice'))
             ->setClass('btn btn-xs btn-info');


        $grid->addAction('delete', '', 'delete!')
             ->setIcon('trash')
             ->setClass('ajax btn btn-xs btn-danger')
             ->setConfirm(function ($item) {
                 return "Opravdu chcete smazat uživatele `{$item->username}`?";
             });


        $presenter = $this;
        $grid->setItemsDetail(__DIR__ . '/templates/Users/#grid_item_detail.latte');

        $devicesGroupsBuilder = $this->deviceGroupRepository->getUserAllowedQueryBuilder($this->getUser());
        $devicesGroupsTree = $this->deviceGroupRepository->buildTreeArray($devicesGroupsBuilder->getQuery()->getArrayResult());

        $devicesGroups = $this->deviceGroupRepository->getCachedResult($devicesGroupsBuilder);
        $devicesGroups = $this->deviceGroupRepository->getAssoc($devicesGroups);

        $grid->setItemsDetailForm(function (Nette\Forms\Container $container) use ($grid, $presenter, $devicesGroups) {

            Debugger::$maxDepth = 4;


//            Debugger::barDump($q->getQuery()->getArrayResult());

//            $queryBuilder = $this->deviceGroupRepository->childrenQueryBuilder($rootDeviceGroupEntity);
//            dump($queryBuilder);




//            Debugger::barDump($devicesGroups);



            $devices = $this->deviceRepository->getCachedResult($this->deviceRepository->getUserAllowedQueryBuilder($this->getUser()));
            $devices = $this->deviceRepository->getAssoc($devices);

            /** @var UserEntity $entity */
            $entity = $this->userRepository->find($container->getName());

//            dump($container->getName());
//            dump($entity);

            $form = $this->userFormFactory->create();

            $form
                ->setFormName($name = 'userForm')
                ->setDevices($devices)
                ->setDevicesGroups($devicesGroups)
                ->setTranslator($this->translator->domain('messages.forms.userForm'))
                ->setNewPassword(Nette\Utils\Random::generate()) // $this->newPassword
                ->setEditActive($this->user->isAllowed($this->name, 'toggleActive'))
                ->setEditRole($this->getEditRoles());


            $form->create($container);
//            $form->create();
//            $form->bootstrap3Render();

//            $form->bindEntity($entity);

            /** @var Nette\Forms\Controls\TextInput $component */
//            $component = $form->getComponent('firstName');
//
//            $component->getRules();


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

//        dump($entity->getDevices());
//        dump($deviceList);

            $container->setDefaults([
                'role' => $entity->getRole(),
                'devices' => $deviceList,
                'devicesGroups' => $deviceGroupList,
            ]);




//            $container->addComponent($form['firstName'], 'firstName');
//            $container->addComponent($form->getComponent('firstName'), 'firstName');

//            $firstName = $container->addText('firstName', 'Jméno');



//            $container->addText('lastName', 'Příjmení');
//            $container->addText('mail', 'E-Mail');
//            $container->addSelect('role', 'Role', $this->getEditRoles());



            /*
                        $container->addHidden('id');
                        $container->addText('name');

                        $container->addSubmit('save', 'Save')
                            ->onClick[] = function($button) use ($grid, $presenter) {
                            $values = $button->getParent()->getValues();

                            $presenter['usersGridControl']->redrawItem($values->id);
                        };
            */

//            Debugger::$maxDepth = 4;
//            dump($container);

//            $container->onValidate[] = function (Nette\Forms\Container $container) use ($grid) {
//
//                $message = "ASADADADDSADS";
//
//                $title      = $this->translateMessage()->translate('userPage.management');
//                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
//
//
//
//                /** @var Nette\Forms\Controls\CheckboxList $component */
//                $component = $container->getComponent('devices');
//
//                $component->addError('ASScc cccc');
//
//
//                $this->ajaxRedirect('this', null, ['flash']);
//
//                $this->payload->_toggle = false;
//
//                $grid->redrawItem("4");
//
////                dump($component);
//
//                return false;
////                $grid->invalidResponse('ASdad');
//            };
//



            $container->addSubmit('save', 'Save')
                ->onClick[] = function(SubmitButton $button) use ($entity, $container, $form, $grid)  {



                if ($container->isValid()) {
                    $this->entityFormMapper->save($entity, $container);
                }


                $values = $button->getParent()->getValues();


//                Debugger::barDump($container, 'container after');


//                dump($values);
//                dump($container->isValid());
//                dump($entity);

//                Debugger::barDump($button);

//                dump($button);
//                die();

                $form->onSave[] = function ($message, UserEntity $entity) {
                    $title      = $this->translateMessage()->translate('userPage.management');
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
                    $this->ajaxRedirect('this', 'usersGridControl', ['flash']);
                };

                $form->onSaveError[] = function ($message) use ($grid) {
                    $title      = $this->translateMessage()->translate('userPage.management');
                    $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_DANGER);
                    $this->ajaxRedirect('this', null, ['flash']);
                    $grid->redrawItem(0); // non exist snippet, overflow grid snippets to ignore redraw anything with out flash
                    return false;
                };

                if ($container->isValid()) {
                    $form->save($entity, $values);
                }

                return false;
            };

        });


        $grid->onRender[] = function (DataGrid $grid) use ($devicesGroupsTree) {


//            $selectedIds = [];
//            foreach ($entity->getDevicesGroups() as $devicesGroup) {
//                $selectedIds[] = $devicesGroup->getId();
//            }



//            $rawTrees = $this->deviceGroupRepository->buildTreeArray($queryBuilder->getQuery()->getArrayResult());
//            $fancyTree = $this->deviceFacade->getFancyTree()->selectByIds($rawTrees, $selectedIds);



            $grid->template->devicesGroupsTree = $devicesGroupsTree;
            $grid->template->deviceGroupListGridControl = $this['deviceGroupListGridControl'];
            $grid->template->deviceGroupsTreeControl = $this['deviceGroupsTreeControl'];
        };


//        $grid->setTemplateFile(__DIR__ . '/templates/Users/#grid_item_detail.latte');

        return $grid;
    }


    /**
     * zobrazení tree skupiny zařízení
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     * @return Multiplier
     */
    protected function createComponentDeviceGroupListGridControl($name)
    {
        $self = $this;

        return new Multiplier(function ($index) use ($self, $name) {

            $grid = new DataGrid();
            $grid->setTranslator($this->translator);

            if ($this->user->isAllowed('Cms:Device', 'listAllDevices')) {
                $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                            ->select('e')
                                            ->andWhere('e.lvl = :level')->setParameter('level', 0)
                                            ->addOrderBy('e.lvl')
                                            ->addOrderBy('e.lft')
                ;




            } else {
                $rootDeviceGroupEntity = $this->deviceFacade->getDeviceGroupRepository()->getUserRootDeviceGroup($this->getUser());

                $query = $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                            ->select('e')
                                            ->andWhere('e.lft > :left')->setParameter('left', $rootDeviceGroupEntity->getLft())
                                            ->andWhere('e.rgt < :right')->setParameter('right', $rootDeviceGroupEntity->getRgt())
                                            ->andWhere('e.root = :root')->setParameter('root', $rootDeviceGroupEntity)
                                            ->andWhere('e.lvl = :level')->setParameter('level', 1)
                                            ->addOrderBy('e.lvl')
                                            ->addOrderBy('e.lft')
                ;
            }


            $grid->setDataSource($query);
            $grid->setTreeView(function ($id) {
                return $this->deviceFacade->getDeviceGroupRepository()->createQueryBuilder('e')
                                          ->where('e.parent = :parent')->setParameter('parent', $id)
                                          ->addOrderBy('e.lvl')
                                          ->addOrderBy('e.lft')
                                          ->getQuery()
                                          ->getResult();

            }, function (DeviceGroupEntity $deviceGroupEntity) {
                return $this->deviceFacade->getDeviceGroupRepository()->childCount($deviceGroupEntity) > 0;
            });


            $grid->addColumnText('name', 'Název')
                 ->addAttributes(['class' => 'btn btn-xs btn-default btn-block']);


            $grid->addGroupAction('Aktivní')->onSelect[] = [$this, 'setActives'];

            $grid->setTemplateFile(__DIR__ . "/templates/Users/#datagrid_devices_groups_tree.latte");

            $grid->onRender[] = function (DataGrid $grid) use ($name) {
//                $grid->template->form = $this['usersGridControl']['items_detail_form'][$name];

                Debugger::$maxDepth = 4;
                Debugger::barDump($this['usersGridControl']->itemsDetail->getForm());
                /** @var ItemDetailForm $form */
                $form = $this['usersGridControl']->itemsDetail->getForm();
//                $container = $form->getComponent(4);

//                $grid->template->form = $this['usersGridControl']->itemsDetail->getForm();
//                $grid->template->form = $container;




                $grid->template->getLatte()->addProvider('formsStack', $form);
                $grid->template->_form = $form;


//                Debugger::barDump($grid->template->getLatte()->getProviders());

            };

            return $grid;
        });
    }


    protected function createComponentDeviceGroupsTreeControl($name)
    {
        $control = $this->deviceFacade->getDeviceGroupsTreeControlFactory()->create();

        return $control;
    }


    /**
     * @todo do userFacade or userRepository
     *
     * @return UserEntity
     */
    protected function getEntity(): UserEntity
    {
        static $entity = null;

        if (!$entity) {
            $entity = $this->editUser
                ? $this->userRepository->find($this->editUser)
                : $this->userFacade->createEmptyUser($this->getUserEntity());
        }

        return $entity;
    }


    private function clearEntity()
    {
        $this->editUser = null;
    }



}