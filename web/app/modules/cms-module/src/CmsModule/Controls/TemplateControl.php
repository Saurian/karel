<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    TemplateControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

use CmsModule\Entities\MediumEntity;
use CmsModule\Entities\TemplateEntity;
use CmsModule\Forms\ITemplateFormFactory;
use CmsModule\Forms\TemplateForm;
use CmsModule\Repositories\MediaRepository;
use Flame\Application\UI\Control;
use Kdyby\Translation\Translator;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\Session;

interface ITemplateControlFactory
{
    /** @return TemplateControl */
    function create();
}

/**
 * Class TemplateControl
 *
 * @package CmsModule\Controls
 * @method  onTemplateFormSuccess(TemplateEntity $entity, TemplateControl $templateForm)
 */
class TemplateControl extends Control
{

    /** @var Translator @inject */
    public $translator;

    /** @var ITemplateFormFactory @inject */
    public $templateFormFactory;

    /** @var Session @inject */
    public $session;

    /** @var MediaRepository @inject */
    public $mediaRepository;


    public $onTemplateFormSuccess = [];

    /** @var TemplateEntity */
    private $templateEntity;



    public function render()
    {
        $template = $this->getTemplate();

        $template->entity = $this->getTemplateEntity();
        $template->render();
    }

    /**
     * @return TemplateEntity
     */
    public function getTemplateEntity($new = false)
    {
        if (!$this->templateEntity || $new) {

            $entity = new TemplateEntity();
            if ($this->mediaRepository->hasTemplateName()) {
                $entity->setName($this->mediaRepository->getTemplateName());
            }
            if ($this->mediaRepository->existMedia()) {
                foreach ($media = $this->mediaRepository->getMedia() as $medium) {
                    $entity->addMedium(new MediumEntity($medium['type']));
                }
            }

            $this->templateEntity = $entity;
        }

        return $this->templateEntity;
    }


    protected function createComponentTemplateForm($name)
    {
        $form = $this->templateFormFactory->create();
        $form->setTranslator($this->translator->domain('messages.forms.' . $name));
        $form->setFormName($name);

        $form->create();

        $entity = $this->getTemplateEntity();

        $form->onAddMedium[] = function ($media) use ($form, $entity) {

            $entity = $this->getTemplateEntity($new = true);
            $form->bindEntity($entity);

            if ($this->presenter->isAjax()) {
                $this->redrawControl('form');

            } else {
                $this->redirect('this');
            }
        };

        $form->onRemoveMedium[] = function ($media) use ($form, $entity) {

            $entity = $this->getTemplateEntity($new = true);
            $form->bindEntity($entity);

            if ($this->presenter->isAjax()) {
                $this->redrawControl('form');

            } else {
                $this->redirect('this');
            }
        };

        $form
            ->bootstrap3Render()
            ->bindEntity($entity)
            ->onSuccess[] = function (TemplateForm $form) {

            /** @var SubmitButton $btnSend */
            $btnSend = $form['sendTemplate'];

            if ($btnSend->isSubmittedBy()) {

                /** @var TemplateEntity $entity */
                $entity = $form->getEntity();
                $this->onTemplateFormSuccess($entity, $this);

                $form->bindEntity($entity = new TemplateEntity());

                if ($this->presenter->isAjax()) {
                    $this->redrawControl('form');

                } else {
                    $this->redirect('this');
                }
            }
        };

        return $form;
    }


}