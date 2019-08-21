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
use CmsModule\Entities\UserEntity;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\UserFacade;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\IUserFormFactory;
use CmsModule\Forms\UserForm;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Nette;
use Nette\Application\UI\Multiplier;
use Tracy\Debugger;

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


    public function actionInit()
    {
        $this->userFacade->initPositions();

    }


    public function actionDefault()
    {
        $this->entity = new UserEntity(null, null, null, null, null);

    }


    public function renderDefault()
    {
        $query = $this->getUserAllowedUsersQuery();
        $rows  = $this->userRepository->fetch($query);

        $total  = $rows->count();
        $active = $nonActive = 0;

        foreach ($rows as $row) {
            if ($row->active) $active++;
            if (!$row->active) $nonActive++;
        }


        $this->template->rows = $this->getRows();

        $this->template->allUserCount       = $total;
        $this->template->activeUserCount    = $active;
        $this->template->nonActiveUserCount = $nonActive;


        $this->template->userEntity = $this->entity;
    }


    /**
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


    /**
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
     * new user form
     *
     * @param $name
     *
     * @return \CmsModule\Forms\UserForm
     */
    protected function createComponentUserForm($name)
    {
//        $devices = $this->deviceFacade->getAllowedDevices($this->user);
        $devices = $this->deviceRepository->getAssocRows($this->deviceRepository->fetch($this->deviceRepository->getUserAllowedQuery($this->user))->getIterator());
        $devicesGroups = $this->deviceFacade->getAllowedDevicesGroups($this->user);

        $form = $this->userFormFactory->create();

        $form
            ->setDevices($devices)
            ->setDevicesGroups($devicesGroups)
            ->setFormName($name)
            ->setTranslator($this->translator->domain('messages.forms.userDetailForm'))
            ->setNewPassword(Nette\Utils\Random::generate()) // $this->newPassword
            ->setEditActive($this->user->isAllowed($this->name, 'toggleActive'))
            ->setEditRole($this->getEditRoles())
            ->addFormClass(['ajax']);

        $form->create();
        $form->bootstrap3Render();
        $form->bindEntity($this->entity);

        $form->onSuccess[] = function (BaseForm $form, $values) {
            $form->setValues([], true);
            $this->payload->_switchery_redraw = true;
            $this->ajaxRedirect('this', null, ['userForm', 'users', 'filter', 'flash']);

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


}