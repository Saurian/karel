<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DevicesControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

interface IDevicesControlFactory
{
    /** @return DevicesControl */
    function create();
}

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Forms\BaseForm;
use CmsModule\Forms\DeviceForm;
use CmsModule\Forms\IDeviceFormFactory;
use CmsModule\Forms\IDeviceGroupFormFactory;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\Queries\DeviceQuery;
use Flame\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Security\User;
use Tracy\Debugger;

class DevicesControl extends Control
{

    /** @var DeviceEntity[] */
    private $rows = [];

    /** @var User @inject */
    public $user;

    /** @var IDeviceFormFactory @inject */
    public $deviceFormFactory;

    /** @var IDeviceGroupFormFactory @inject */
    public $deviceGroupFormFactory;

    /** @var DeviceRepository @inject */
    public $deviceRepository;


    public function handleSetFilter($active)
    {
        $this->deviceRepository->setFilterActive($active);

        $filter    = $this->deviceRepository->getFilterActive();

        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        $message = "Nastaven filtr";
        if ($filter === "1") {
            $message = "Nastaven filtr `Správa aktivních zařízení`";

        } elseif ( $filter === "0") {
            $message = "Nastaven filtr `Správa neaktivních zařízení`";

        } elseif ( $filter === null) {
            $message = "Nastaven filtr `Správa všech zařízení`";
        }

        $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa Zařízení', FlashMessageControl::TOAST_INFO);

        $this->redrawControl();
        $presenter->redrawControl('flash');

    }



    public function handleToggleActive($id, $checked)
    {
        /** @var BasePresenter $presenter */
        $presenter = $this->getPresenter();

        /** @var DeviceEntity $element */
        if ($element = $this->deviceRepository->find($id)) {
            $em = $this->deviceRepository->getEntityManager();

            $element->setActive($checked);
            $em->persist($element)->flush();

            $message = "Zařízení `{$element->name}` je nyní " . ($element->isActive() ? 'aktivní' : 'neaktivní');
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


    public function handleDetail()
    {
//        $form = new DeviceForm();
//        $form->create();


//        $this->form = $form;

        if ($this->presenter->isAjax())
//            $this->redrawControl();
//            $this->redrawControl('devicesItems');
            $this->redrawControl('items');


    else $this->redirect('this');
    }



    public function render()
    {
        $template = $this->getTemplate();
        $template->rows = $this->getRows();

        $template->allDeviceCount       = $this->getAllRowsCount();
        $template->activeDeviceCount    = $this->getActiveRowsCount();
        $template->nonActiveDeviceCount = $this->getNonActiveRowsCount();

        $template->render();

    }

    public function getActiveRowsCount()
    {
        $query = (new DeviceQuery())->isActive();
        return $this->deviceRepository->fetch($query)->getTotalCount();
    }

    public function getNonActiveRowsCount()
    {
        $query = (new DeviceQuery())->isNotActive();
        return $this->deviceRepository->fetch($query)->getTotalCount();
    }

    public function getAllRowsCount()
    {
        $query = (new DeviceQuery());
        return $this->deviceRepository->fetch($query)->getTotalCount();
    }



    /**
     * @return DeviceEntity[]
     */
    public function getRows()
    {
        if (!$this->rows) {
            $query = (new DeviceQuery());

            if (!$this->user->isAllowed('Cms:Device', 'listAllDevices')) {
                $query->byUser($this->user);
            }


            if ($this->deviceRepository->existFilterActive()) {
                $filterActive = $this->deviceRepository->getFilterActive();

                switch ($filterActive) {
                    case true:
                        $query->isActive(); break;

                    case false:
                        $query->isNotActive(); break;
                }
            }

//            $q = $this->deviceRepository->fetch($query);
//            dump($q->count());


            $this->setRows($this->deviceRepository->fetch($query));

        }


//        dump($this->rows);
//        die();


        return $this->rows;
    }


    /**
     * @param DeviceEntity[] $rows
     */
    public function setRows($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->getId()] = $row;
        }

        $this->rows = $_rows;
    }




    protected function createComponentDeviceDetailForm($name)
    {
        $self = $this;

        return new Multiplier(function ($index) use ($self, $name) {

//            Debugger::barDump($self->rows);

            $form = $this->deviceFormFactory->create();
            $form->setFormName($name);
            $form->setId($index);

            $form->create();
            $form->bootstrap3Render();
            $form->bindEntity($entity = $self->getRows()[$index]);
            $form->onSuccess[] = function (DeviceForm $form, $values) {

                if ($this->presenter->isAjax()) {
                    $this->redrawControl('items');
//                    $this->redrawControl('devicesItems');
//                    $this->redrawControl();

                    $presenter = $this->presenter;
//                    $presenter->redrawControl();

                    $presenter->redrawControl('flash');
                    $form->setValues([], true);

                } else {
                    $this->redirect('this');
                }

            };


            return $form;
        });
    }


    protected function createComponentDeviceGroupForm($name)
    {
        $form = $this->deviceGroupFormFactory->create();

        $form->setFormName($name);

        $form->create();
//        Debugger::barDump($form->name);


        $form->bootstrap3Render();
        $form->bindEntity($entity = new DeviceGroupEntity());
        $form->onSuccess[] = function (BaseForm $form, $values) {

            if ($this->presenter->isAjax()) {
                $form->setValues([], true);

                $this->redrawControl('items');
//                $this->getPresenter()->redrawControl();
                $this->getPresenter()->redrawControl('flashMessage');


            } else {
                $this->redirect('this');
            }

        };

        return $form;
    }


}

