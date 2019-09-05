<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    DeviceControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Forms\DeviceForm;
use CmsModule\Forms\IDeviceFormFactory;
use Flame\Application\UI\Control;

interface IDeviceControlFactory
{
    /** @return DeviceControl */
    function create();
}

class DeviceControl extends Control
{

    /** @var IDeviceFormFactory @inject */
    public $deviceFormFactory;


    public function render()
    {
        $template = $this->getTemplate();


//        dump($this->deviceFormFactory);

        $template->render();

    }


    protected function createComponentDeviceForm($name)
    {
        $form = $this->deviceFormFactory->create();
        $form->create();

        $entity = new DeviceEntity();

        $form
            ->bootstrap3Render()
            ->bindEntity($entity)
            ->onSuccess[] = function (DeviceForm $form) {

            $entity = $form->getEntity();
//            $em = $form->getEntityMapper()->getEntityManager();

//            $em->persist($entity)->flush();



//            dump($entity);
//            die();


            if ($this->presenter->isAjax()) {
                $this->redrawControl();
                $this->presenter->redrawControl('flash');
                $this->presenter->redrawControl('flash');

            } else {
                $this->redirect('this');
            }

        };


        return $form;
    }


}