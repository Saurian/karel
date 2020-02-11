<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UserForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\LogEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Facades\UserFacade;
use CmsModule\Presenters\BasePresenter;
use CmsModule\Repositories\DeviceGroupRepository;
use CmsModule\Repositories\DeviceRepository;
use CmsModule\Repositories\UserRepository;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kdyby\Monolog\Logger;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Security\User;

interface IUserFormFactory
{
    /** @return UserForm */
    function create();
}

/**
 * Class UserForm
 * @package CmsModule\Forms
 * @method onSendEmail(UserEntity $userEntity, UserForm $form)
 * @method onResetPassword($message, UserEntity $userEntity, UserForm $form)
 * @method onSave($message, UserEntity $userEntity, UserForm $form)
 * @method onSaveError($message, UserForm $form)
 */
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

    /** @var array */
    private $roles = [];

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


    /** @var array callback */
    public $onSendEmail = [];

    /** @var array callback */
    public $onSave = [];

    /** @var array callback */
    public $onResetPassword = [];

    /** @var array callback */
    public $onSaveError = [];

    /** @var string messages.forms.userForm. */
    private $domainPrefix = '';


    public function create(Container $container = null)
    {
        $form = $container ? $container : $this;

        $form->addSubmit('sendSubmit', 'sendUser')
            ->setAttribute('class', 'btn btn-success'); // box-list__settings__close

        $form->addText('firstName', "{$this->domainPrefix}first_name")
            ->setDisabled($this->disAllowed)
            ->setAttribute('placeholder', "{$this->domainPrefix}placeholder.first_name")
            ->addRule(Form::FILLED, "{$this->domainPrefix}filled")
            ->addRule(Form::MIN_LENGTH, new Phrase('min', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('max', 255), 255);

        $form->addText('lastName', "{$this->domainPrefix}last_name")
            ->setDisabled($this->disAllowed)
            ->setAttribute('placeholder', "{$this->domainPrefix}placeholder.last_name")
            ->addRule(Form::FILLED, "{$this->domainPrefix}filled")
            ->addRule(Form::MIN_LENGTH, new Phrase('min', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('max', 255), 255);

        $form->addText('mail', "{$this->domainPrefix}email")
            ->setDisabled($this->disAllowed)
            ->setAttribute('placeholder', "{$this->domainPrefix}placeholder.email")
            ->addRule(Form::FILLED, "{$this->domainPrefix}filled")
            ->addRule(Form::EMAIL, "{$this->domainPrefix}valid_email");

        if ($this->editActive) {
            $form->addCheckbox('active', "{$this->domainPrefix}active")
                ->setAttribute('placeholder', "active")
                ->setAttribute('class', 'js-switch')
                ->setAttribute('data-size', 'small');
        }


        /*[
            'watcher'    => "{$this->domainPrefix}role.watcher",
            'editor'     => "{$this->domainPrefix}role.editor",
            'master'     => "{$this->domainPrefix}role.master",
            'admin'      => "{$this->domainPrefix}role.admin",
            'supervisor' => "{$this->domainPrefix}role.supervisor",
        ]*/

        $form->addSelect('role', $this->getTranslator()->translate("{$this->domainPrefix}role.role"), $this->roles)
            ->setTranslator(null)
            ->setDisabled(!$this->editRole)
            ->setPrompt($this->getTranslator()->translate("{$this->domainPrefix}select_please"))
            ->addRule(Form::FILLED, "{$this->domainPrefix}filled");


        $devices = $form->addCheckboxList('devices', $this->getTranslator()->translate('devices'), $this->getDeviceNames())
            ->setDisabled($this->disAllowed)
            ->setTranslator(null)
            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;


        $devicesGroups = $form->addCheckboxList('devicesGroups', "{$this->domainPrefix}devices_groups", $this->getDeviceGroupsNames())
            ->setDisabled($this->disAllowed)
            ->setTranslator(null)
            ->setOption(IComponentMapper::FIELD_IGNORE, true)
            ->setOption(IComponentMapper::ITEMS_TITLE, 'name')
            ->setOption(IComponentMapper::ITEMS_FILTER, ['id' => null]);  // trick, we dont want autoload items;

        $devices
            ->addCondition(Form::BLANK)
            ->addConditionOn($form['devicesGroups'], Form::BLANK)
            ->addRule(Form::FILLED, "{$this->domainPrefix}ruleDeviceOrGroup");

        $devicesGroups
            ->addCondition(Form::BLANK)
            ->addConditionOn($form['devices'], Form::BLANK)
            ->addRule(Form::FILLED, "{$this->domainPrefix}ruleDeviceOrGroup");

//        $form->addSubmit('save')
//            ->setAttribute('class', 'box-newTemplate__adding__new _js-addNewTemplateMedia')
//            ->setValidationScope(FALSE)
//            ->onClick[] = [$this, 'addUser'];


        $this->onSuccess[] = [$this, 'success'];

//        $this->addFormClass(['ajax', 'auto-save']);
        $this->getElementPrototype()->addAttributes(['data-name' => $this->formName, 'data-id' => $this->getId()]);
        return $this;
    }


    public function success(BaseForm $form, $values)
    {
        /** @var UserEntity $entity */
        $entity = $form->getEntity();

//        dump($_POST);
//        dump($values);
//        dump($this->getPresenter()->getParameters());
//        die;


        $this->save($entity, $values);
    }


    public function save(UserEntity $entity, $values)
    {
        /** @var BasePresenter $presenter */
//        $presenter  = $this->getPresenter();
        $translator = $this->getTranslator();
        $title      = $translator->translate('userPage.management');
        $passwordSet = false;

        if (!$entity->getPassword()) {
            $entity->setPassword($newPsw = $this->newPassword);
            $passwordSet = true;
        }

        try {
            $em = $this->getEntityMapper()->getEntityManager();

            /** @var UserEntity $entity */
//            $entity              = $form->getEntity();
            $selectDevices       = (array)$values->devices;
            $selectDevicesGroups = (array)$values->devicesGroups;
            $devices             = $this->getDevices();
            $devicesGroups       = $this->getDevicesGroups();

            $this->userFacade->updateDevices($entity, $devices, $selectDevices);
            $this->userFacade->updateDevicesGroups($entity, $devicesGroups, $selectDevicesGroups);

            /*
             * messages
             */
            $newUser = $entity->getId() == null;
            $saveMessage = $entity->getId()
                ? $this->getTranslator()->translate("{$this->domainPrefix}user_updated", null, ['user' => $entity->getUsername()])
                : $this->getTranslator()->translate("{$this->domainPrefix}user_added", null, ['user' => $entity->getFullName()]);

            $em->persist($entity)->flush();

            if ($passwordSet) {
                $this->onResetPassword($this->newPassword, $entity, $this);
            }

            if ($this->emailSending && $newUser) {
                $this->sendMail($entity);
            }

            $this->onSave($saveMessage, $entity, $this);

        } catch (NotNullConstraintViolationException $e) { // 1048
            $message = $translator->translate('user_form_error', $e->getErrorCode());

            $this->logger->warning("user [{$entity->getRole()}] `{$entity->getName()}` has been inserted error [{$e->getMessage()}]", ['type' => LogEntity::ACTION_FORM, 'target' => $entity, 'action' => 'user inserted']);
            $this->onSaveError($message, $this);

        } catch (UniqueConstraintViolationException $e) {
            $message = $translator->translate('user_form_duplicate_error', $e->getErrorCode());

            $this->logger->warning("user [{$entity->getRole()}] `{$entity->getName()}` has been inserted error [{$e->getMessage()}]", ['type' => LogEntity::ACTION_FORM, 'target' => $entity, 'action' => 'user inserted']);
            $this->onSaveError($message, $this);

//            $em = $form->getEntityMapper()->getEntityManager();
//            $em->detach($entity);
//            $em->flush();
        }

    }


    /**
     * send email
     *
     * @param UserEntity $entity
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    private function sendMail(UserEntity $entity)
    {
        /** @var BasePresenter $presenter */
        $presenter  = $this->getPresenter();

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

        $this->onSendEmail($entity, $this);
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
//        dump($entity);
//        dump($values);
//        die;

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
     * @param array $roles
     * @return UserForm
     */
    public function setRoles(array $roles): UserForm
    {
        $this->roles = $roles;
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