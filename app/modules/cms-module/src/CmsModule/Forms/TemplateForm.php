<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    TemplateForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\TemplateEntity;
use CmsModule\Repositories\MediaRepository;
use Kdyby\Events\Event;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

interface ITemplateFormFactory
{
    /** @return TemplateForm */
    function create();
}

/**
 * Class TemplateForm
 *
 * @package CmsModule\Forms
 * @method  onTemplateFormSuccess(TemplateEntity $entity, TemplateForm $templateForm)
 * @method  onAddMedium(array $media, TemplateForm $templateForm)
 * @method  onRemoveMedium(array $media, TemplateForm $templateForm)
 */
class TemplateForm extends BaseForm
{

    protected $autoButtonClass = false;

    /** @var MediaRepository @inject */
    public $mediaRepository;

    /** @var Event */
    public $onTemplateFormSuccess = [];

    /** @var Event */
    public $onAddMedium = [];

    /** @var Event */
    public $onRemoveMedium = [];




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

        $this->addSubmit('addMediumSubmit')
            ->setAttribute('class', 'box-newTemplate__adding__new _js-addNewTemplateMedia')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addMedium'];


        $this->addSubmit('sendTemplate', 'add_template')
            ->setAttribute('class', 'btn btn-success _waves-effect _waves-light btn-lg  js-newTemplateSubmit')
            ->onClick[] = [$this, 'success'];

//        $this->onSuccess[] = [$this, 'success'];
        $this->getElementPrototype()->addAttributes(['data-name' => $this->formName, 'data-id' => $this->getId()]);

        $this->addFormClass(['ajax']);
        return $this;
    }


    public function success(SubmitButton $btn)
    {
        /** @var BaseForm $form */
        $form = $btn->getForm();

        $this->mediaRepository->clearMedia();
    }


    public function addMedium(SubmitButton $btn)
    {
        /** @var BaseForm $form */
        $form   = $btn->getForm();
        $values = $form->getValues();
        $_media = $values->media;
        $media  = [];

        foreach ($_media as $medium) {
            $media[] = $medium;
        }

        $media[] = ['type' => 'image'];

        $this->mediaRepository->setTemplateName($values->name);
        $this->mediaRepository->saveMedia($media);
        $this->onAddMedium($media, $this);
    }

    public function removeMedium(SubmitButton $btn)
    {
        /** @var BaseForm $form */
        $form   = $btn->getForm();
        $values = $form->getValues();
        $_media = $values->media;
        $media  = [];

        $id    = $btn->getParent()->getName();
        $keys  = array_keys((array)$_media);
        $index = array_search($id, $keys);
        unset($_media[$id]);

        foreach ($_media as $medium) {
            $media[] = $medium;
        }

        $this->mediaRepository->removeMedium($index);
        $this->mediaRepository->setTemplateName($values->name);
        $this->onRemoveMedium($media, $this);
    }


}