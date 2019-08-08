<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    RegistrationForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\UserEntity;
use CmsModule\Presenters\BasePresenter;
use Kdyby\Translation\Phrase;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

interface IRegistrationFormFactory
{
    /** @return RegistrationForm */
    function create();
}

class RegistrationForm extends BaseForm
{

    /** @var IMailer @inject */
    public $mailer;

    /** @var bool sending email? (DI) */
    private $emailSending = true;

    /** @var string sending from email? (DI) */
    private $emailFrom;

    /** @var bool */
    private $emailFlashMessage = false;


    public function create()
    {

        $this->addText('firstName', 'first_name')
            ->setAttribute('placeholder', "placeholder.first_name")
            ->addRule(Form::FILLED, 'ruleFirstName')
            ->addRule(Form::MIN_LENGTH, new Phrase('ruleMinLength', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('ruleMaxLength', 255), 255);

        $this->addText('lastName', 'last_name')
            ->setAttribute('placeholder', "placeholder.last_name")
            ->addRule(Form::FILLED, 'ruleLastName')
            ->addRule(Form::MIN_LENGTH, new Phrase('ruleMinLength', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('ruleMaxLength', 255), 255);

        $this->addText('mail', 'email')
            ->setAttribute('placeholder', "placeholder.email")
            ->addRule(Form::FILLED, 'ruleEMail')
            ->addRule(Form::EMAIL, 'valid_email');

        $password = $this->addPassword('password', 'password');
        $password->setAttribute('placeholder', "placeholder.password")
            ->addRule(Form::FILLED, 'rulePassword')
            ->addRule(Form::MIN_LENGTH, new Phrase('ruleMinLength', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('ruleMaxLength', 255), 255);

        $this->addPassword('password2', 'confirm_password')
            ->setAttribute('placeholder', "placeholder.confirm_password")
            ->addRule(Form::FILLED, 'ruleConfirmPassword')
            ->addConditionOn($password, Form::FILLED)
            ->addRule(Form::MIN_LENGTH, new Phrase('ruleMinLength', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('ruleMaxLength', 255), 255)
            ->addRule(Form::EQUAL, 'password_dont_match', $password);

        $this->addCheckbox('remember', 'remember')->getControl()->class[] = 'icheck';
        $this->addSubmit('send', 'send')->setAttribute('class', 'btn btn-lg btn-primary btn-block text-uppercase');
        $this->onSuccess[] = array($this, 'formSuccess');

        $this->onValidate[] = [$this, 'validateEmail'];

        return $this;

    }


    public function validateEmail(RegistrationForm $form, $values) {

        $mail = $values->mail;
        $em = $this->getEntityMapper()->getEntityManager();

        if ($existUserEntity = $em->getRepository(UserEntity::class)->findOneBy(['mail' => $mail])) {
            /** @var Translator $translator */
            $translator = $this->getTranslator();
            $this->addError($translator->translate('user_form_duplicate_error', ['mail' => $mail]), false );
            return false;
        }

        return true;
    }


    public function formSuccess(RegistrationForm $form, $values)
    {

        /** @var UserEntity $entity */
        $entity = $form->getEntity();
        $newUser = $entity->getId() == null;

        if ($this->emailSending && $newUser) {
            $this->sendMail($entity);
        }

    }


    /**
     * send email
     *
     * @param $values
     */
    private function sendMail(UserEntity $values)
    {
        /** @var BasePresenter $presenter */
        $presenter  = $this->getPresenter();
        $translator = $this->getTranslator();
        $title      = $presenter->translateMessage()->translate('userPage.management');

        $latte  = new \Latte\Engine;
        $params = [
            'url'      => $presenter->link("//:Cms:Campaign:"),
            'urlLogin' => $presenter->link("//:Cms:Login:", ['code' => $values->getPassword()]),
//            'username' => $values->mail,
//            'password' => $values->password,
        ];

        $message = new Message();
        $message->setFrom($this->emailFrom)
            ->addTo($values->getMail())
            ->setHtmlBody($latte->renderToString(__DIR__ . "/template/registrationEmail.latte", $params));

        $this->mailer->send($message);

        if ($this->emailFlashMessage) {
            $message = $translator->translate('user_has_been_send_email');
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
        }
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


}