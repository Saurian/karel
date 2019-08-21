<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    ChangePasswordForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use Kdyby\Monolog\Logger;
use Kdyby\Translation\Phrase;
use Nette\Forms\Form;
use Nette\Mail\IMailer;

interface IChangePasswordFormFactory
{
    /** @return ChangePasswordForm */
    function create();
}

/**
 * Class ChangePasswordForm
 *
 * @package CmsModule\Forms
 */
class ChangePasswordForm extends BaseForm
{

    /** @var IMailer @inject */
    public $mailer;

    /** @var Logger @inject */
    public $logger;


    /** @var bool sending email? (DI) */
    private $emailSending = true;

    /** @var string sending from email? (DI) */
    private $emailFrom;



    /**
     * @return ChangePasswordForm
     */
    public function create()
    {
        $this->addText('mail', 'email')
            ->setAttribute('placeholder', "placeholder.email")
            ->setDisabled()
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
            ->addRule(Form::MIN_LENGTH, new Phrase('ruleMinLength', 3), 3)
            ->addRule(Form::MAX_LENGTH, new Phrase('ruleMaxLength', 255), 255)
            ->addConditionOn($password, Form::FILLED)
            ->addRule(Form::EQUAL, 'password_dont_match', $password);

        $this->addSubmit('send', 'send')->setAttribute('class', 'btn btn-lg btn-inverse btn-block text-uppercase');
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
    public function setEmailFrom($emailFrom)
    {
        $this->emailFrom = $emailFrom;
        return $this;
    }



}