<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    AdminTemplateForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\MediumEntity;
use CmsModule\Entities\TemplateEntity;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

interface IAdminTemplateFormFactory
{
    /** @return AdminTemplateForm */
    function create();
}

class AdminTemplateForm extends BaseForm
{

    public function create()
    {
        $this->addText('name', 'name')
            ->setAttribute('placeholder', "template_name")
            ->addRule(Form::FILLED, 'filled')
            ->addRule(Form::MIN_LENGTH, new Phrase('min', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('max', 255), 255);

        $removeEvent = [$this, 'removeMedium'];
        $this->toMany('media', function (\Nette\Forms\Container $medium) use ($removeEvent) {
            $medium->addRadioList('type', 'Typ media', [
                'image' => 'image',
                'video' => 'video',
                'url' => 'url',
                'zip' => 'zip',
            ]);

            $medium->addSubmit('removeMediumSubmit', 'x')
                ->setTranslator(null)
                ->setAttribute('class', 'js-removeTemplateOne')
                ->setValidationScope(FALSE)
                ->onClick[] = $removeEvent;
        });

        $this->addSubmit('addMediumSubmit', 'addMedium')
            ->setAttribute('class', 'btn btn-xs btn-primary')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addMedium'];


        $this->addSubmit('sendTemplate', 'sendTemplate')
            ->setAttribute('class', 'btn btn-xs btn-success')
            ->onClick[] = [$this, 'success'];

//        $this->onSuccess[] = [$this, 'success'];

        $this->addFormClass(['ajax']);
        return $this;
    }



    public function success(SubmitButton $btn)
    {
        /** @var BaseForm $form */
        $form = $btn->getForm();

        /** @var TemplateEntity $entity */
        $entity = $form->getEntity();
        $em     = $form->getEntityMapper()->getEntityManager();

        $em->persist($entity)->flush();

    }


    public function addMedium(SubmitButton $btn)
    {
        /** @var BaseForm $form */
        $form  = $btn->getForm();
        $_media = $form->values->media;
        $media = [];

        foreach ($_media as $medium) {
            $media[] = $medium;
        }

        $media[] = ['type' => 'image'];


        /** @var TemplateEntity $entity */
        $entity = $form->getEntity();
        $entity->addMedium($mediumEntity = new MediumEntity('image'));

        $em = $form->getEntityMapper()->getEntityManager();
        $em->persist($entity)->flush();


        $presenter = $this->getPresenter();
        $presenter->redrawControl('form');
        $presenter->redrawControl();
    }

    public function removeMedium(SubmitButton $btn)
    {
        /** @var BaseForm $form */
        $form  = $btn->getForm();
        $_media = $form->values->media;
        $media = [];

        $id    = $btn->getParent()->getName();
        $keys  = array_keys((array)$_media);
        $index = array_search($id, $keys);
        unset($_media[$id]);

        foreach ($_media as $medium) {
            $media[] = $medium;
        }


    }




}