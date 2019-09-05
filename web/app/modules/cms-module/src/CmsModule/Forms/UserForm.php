<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UserForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\LogEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Facades\UserFacade;
use CmsModule\InvalidArgumentException;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\UserRepository;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kdyby\Monolog\Logger;
use Kdyby\Translation\Phrase;
use Kdyby\Translation\PrefixedTranslator;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Security\User;
use Tracy\Debugger;

interface IUserFormFactory
{
    /** @return UserForm */
    function create();
}

class UserForm extends BaseForm
{

    /** @var Logger @inject */
    public $logger;

    /** @var IMailer @inject */
    public $mailer;

    /** @var User @inject */
    public $user;

    /** @var UserFacade @inject */
    public $userFacade;


    /** @var DeviceRepository @inject */
    public $deviceRepository;

    /** @var DeviceGroupRepository @inject */
    public $deviceGroupRepository;

    /** @var UserRepository @inject */
    public $userRepository;


    protected $autoButtonClass = false;

    /** @var string newPassword */
    private $newPassword;

    /** @var bool */
    private $editRole = true;

    /** @var bool */
    private $editActive = false;

    /** @var bool sending email? (DI) */
    private $emailSending = true;

    /** @var string sending from email? (DI) */
    private $emailFrom;

    /** @var DeviceEntity[] */
    private $devices = [];

    /** @var DeviceGroupEntity[] */
    private $devicesGroups = [];

    /** @var boolean */
    private $disAllowed = false;

    public function create(Container $container = null)
    {
        $form = $container ? $container : $this;

        $form->addSubmit('sendSubmit', 'sendUser')
            ->setAttribute('class', 'btn btn-success box-list__settings__close js-settingsClose');

        $form->addText('firstName', 'first_name')
            ->setDisabled($this->disAllowed)
            ->setAttribute('placeholder', "placeholder.first_name")
            ->addRule(Form::FILLED, 'filled')
            ->addRule(Form::MIN_LENGTH, new Phrase('min', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('max', 255), 255);

        $form->addText('lastName', 'last_name')
            ->setDisabled($this->disAllowed)
            ->setAttribute('placeholder', "placeholder.last_name")
            ->addRule(Form::FILLED, 'filled')
            ->addRule(Form::MIN_LENGTH, new Phrase('min', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('max', 255), 255);

        $form->addText('mail', 'email')
            ->setDisabled($this->disAllowed)
            ->setAttribute('placeholder', "placeholder.email")
            ->addRule(Form::FILLED, 'filled')
            ->addRule(Form::EMAIL, 'valid_email');

        if ($this->editActive) {
            $form->addCheckbox('active', 'active')
                ->setAttribute('placeholder', "active")
                ->setAttribute('class', 'js-switch')
                ->setAttribute('data-size', 'small');
        }


        $form->addSelect('role', 'role.role', [
            'watcher'    => 'role.watcher',
            'editor'     => 'role.editor',
            'master'     => 'role.master',
            'admin'      => 'role.admin',
            'supervisor' => 'role.supervisor',
        ])
            ->setDisabled($this->editRole)
            ->setPrompt('select_please')
            ->addRule(Form::FILLED, 'filled');


        $devices = $form->addCheckboxList('devices', 'devices', $this->getDeviceNames())
            ->setDisabled($this->disAllowed)
            ->setTranslator(null)
            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;


        $devicesGroups = $form->addCheckboxList('devicesGroups', 'devices_groups', $this->getDeviceGroupsNames())
            ->setDisabled($this->disAllowed)
            ->setTranslator(null)
            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $devices
            ->addCondition(Form::BLANK)
            ->addConditionOn($this['devicesGroups'], Form::BLANK)
            ->addRule(Form::FILLED, 'ruleDeviceOrGroup');

        $devicesGroups
            ->addCondition(Form::BLANK)
            ->addConditionOn($this['devices'], Form::BLANK)
            ->addRule(Form::FILLED, 'ruleDeviceOrGroup');

        $form->addSubmit('addUserSubmit')
            ->setAttribute('class', 'box-newTemplate__adding__new _js-addNewTemplateMedia')
            ->setValidationScope(FALSE)
            ->onClick[] = [$this, 'addUser'];


        $this->onSuccess[] = [$this, 'success'];

//        $this->addFormClass(['ajax', 'auto-save']);
        $this->getElementPrototype()->addAttributes(['data-name' => $this->formName, 'data-id' => $this->getId()]);
        return $this;
    }


    public function success(BaseForm $form, $values)
    {
        /** @var UserEntity $entity */
        $entity = $form->getEntity();

        /** @var BasePresenter $presenter */
        $presenter  = $this->getPresenter();
        $translator = $this->getTranslator();
        $title      = $presenter->translateMessage()->translate('userPage.management');
        $passwordSet = false;

        if (!$entity->getPassword()) {
            $entity->setPassword($this->newPassword);
            $passwordSet = true;
        }

        try {
            $em = $form->getEntityMapper()->getEntityManager();

            /** @var UserEntity $entity */
            $entity              = $form->getEntity();
            $selectDevices       = (array)$values->devices;
            $selectDevicesGroups = (array)$values->devicesGroups;
            $devices             = $this->getDevices();
            $devicesGroups       = $this->getDevicesGroups();

            $this->userFacade->updateDevices($entity, $devices, $selectDevices);
            $this->userFacade->updateDevicesGroups($entity, $devicesGroups, $selectDevicesGroups);

            $em->persist($entity)->flush();

            if ($passwordSet) {
                $message = $translator->translate('user_password', null, ['password' => $this->newPassword]);
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
            }

            /*
             * messages
             */
            $newUser = $entity->getId() == null;
            $message = $entity->getId()
                ? $this->getTranslator()->translate("user_updated", null, ['user' => $entity->getUsername()])
                : $this->getTranslator()->translate("user_added");

            if ($this->emailSending && $newUser) {
                $this->sendMail($entity);
            }

            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);

        } catch (NotNullConstraintViolationException $e) { // 1048
            $message = $translator->translate('user_form_error', $e->getErrorCode());

            $presenter->flashMessage($message,
                FlashMessageControl::TOAST_TYPE,
                $title, FlashMessageControl::TOAST_DANGER);
            $this->logger->warning("user [{$entity->getRole()}] `{$entity->getName()}` has been inserted error [{$e->getMessage()}]", ['type' => LogEntity::ACTION_FORM, 'target' => $entity, 'action' => 'user inserted']);

        } catch (UniqueConstraintViolationException $e) {
            $message = $translator->translate('user_form_duplicate_error', $e->getErrorCode());

            $presenter->flashMessage($message,
                FlashMessageControl::TOAST_TYPE,
                $title, FlashMessageControl::TOAST_DANGER);

            $this->logger->warning("user [{$entity->getRole()}] `{$entity->getName()}` has been inserted error [{$e->getMessage()}]", ['type' => LogEntity::ACTION_FORM, 'target' => $entity, 'action' => 'user inserted']);
//            $em = $form->getEntityMapper()->getEntityManager();
//            $em->detach($entity);
//            $em->flush();
        }


    }


    /**
     * send email
     *
     * @param $entity
     */
    private function sendMail(UserEntity $entity)
    {
        /** @var BasePresenter $presenter */
        $presenter  = $this->getPresenter();
        $translator = $this->getTranslator();
        $title      = $presenter->translateMessage()->translate('userPage.management');

        $latte  = new \Latte\Engine;
        $params = [
            'url'      => $presenter->link("//:Cms:Login:"),
            'username' => $entity->getUsername(),
            'password' => $this->newPassword,
        ];

        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($entity->mail)
            ->setHtmlBody($latte->renderToString(__DIR__ . "/template/email.latte", $params));

        $this->mailer->send($mail);

        $message = $translator->translate('user_has_been_send_email', $entity->mail);
        $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
    }


    /**
     * @return DeviceEntity[]
     */
    private function getDevices()
    {
        return $this->devices;
    }

    /**
     * @return array
     */
    private function getDeviceNames()
    {
        $_devices = [];
        foreach ($this->devices as $device) {
            $_devices[$device->getId()] = $device->getName();
        }

        return $_devices;
    }

    /**
     * @return DeviceGroupEntity[]
     */
    private function getDevicesGroups()
    {
        return $this->devicesGroups;
    }

    /**
     * @return array
     */
    private function getDeviceGroupsNames()
    {
        $_devicesGroups = [];
        foreach ($this->devicesGroups as $devicesGroup) {
            $_devicesGroups[$devicesGroup->getId()] = $devicesGroup->getName();
        }

        return $_devicesGroups;
    }

    /**
     * @param DeviceEntity[] $devices
     *
     * @return $this
     */
    public function setDevices($devices)
    {
        $this->devices = $devices;
        return $this;
    }

    /**
     * @param DeviceGroupEntity[] $devicesGroups
     *
     * @return $this
     */
    public function setDevicesGroups($devicesGroups)
    {
        $this->devicesGroups = $devicesGroups;
        return $this;
    }


    /**
     * @param string $newPassword
     *
     * @return $this
     */
    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    /**
     * @param bool $editRole
     *
     * @return $this
     */
    public function setEditRole($editRole)
    {
        $this->editRole = $editRole;
        return $this;
    }

    /**
     * @param bool $editActive
     *
     * @return $this
     */
    public function setEditActive($editActive)
    {
        $this->editActive = $editActive;
        return $this;
    }

    /**
     * @param bool $emailSending
     *
     * @return $this
     */
    public function setEmailSending($emailSending)
    {
        $this->emailSending = (bool)$emailSending;
        return $this;
    }

    /**
     * @param string $emailFrom
     *
     * @return $this
     */
    public function setEmailFrom(string $emailFrom)
    {
        $this->emailFrom = $emailFrom;
        return $this;
    }

    /**
     * @param bool $disAllowed
     *
     * @return $this
     */
    public function setDisAllowed(bool $disAllowed)
    {
        $this->disAllowed = $disAllowed;
        return $this;
    }



}