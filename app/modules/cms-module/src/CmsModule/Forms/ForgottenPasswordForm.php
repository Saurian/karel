<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    ForgottenPasswordForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\LogEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Presenters\BasePresenter;
use Kdyby\Monolog\Logger;
use Kdyby\Translation\Phrase;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

interface IForgottenPasswordFormFactory
{
    /** @return ForgottenPasswordForm */
    function create();
}

class ForgottenPasswordForm extends BaseForm
{

    /** @var IMailer @inject */
    public $mailer;

    /** @var Logger @inject */
    public $logger;


    /** @var bool sending email? (DI) */
    private $emailSending = true;

    /** @var string sending from email? (DI) */
    private $emailFrom;

    /** @var UserEntity */
    private $existUserEntity = null;


    /**
     * @return ForgottenPasswordForm
     */
    public function create()
    {
        $this->addText('mail', 'email')
            ->setAttribute('placeholder', "placeholder.email")
            ->addRule(Form::FILLED, 'ruleEMail')
            ->addRule(Form::EMAIL, 'valid_email');


        $this->addSubmit('send', 'send')->setAttribute('class', 'btn btn-lg btn-inverse btn-block text-uppercase');
        $this->onSuccess[] = array($this, 'formSuccess');

        $this->onValidate[] = [$this, 'validateEmail'];
        return $this;
    }


    public function validateEmail(ForgottenPasswordForm $form, $values) {

        $mail = $values->mail;

        if (!$existUserEntity = $this->getExistUserEntity($mail)) {
            /** @var Translator $translator */
            $translator = $this->getTranslator();
            $this->addError($translator->translate('user_form_not_exist', ['mail' => $mail]), false );
            return false;
        }

        return true;
    }


    public function formSuccess(ForgottenPasswordForm $form, $values)
    {

        if ($this->emailSending) {
            $this->sendMail($values->mail);
        }
    }



    /**
     * @return UserEntity
     */
    private function getExistUserEntity($mail)
    {
        if (null === $this->existUserEntity) {
            $em = $this->getEntityMapper()->getEntityManager();
            $this->existUserEntity = $em->getRepository(UserEntity::class)->findOneBy(['mail' => $mail]);
        }

        return $this->existUserEntity;
    }


    /**
     * send email
     *
     * @param $mail
     */
    private function sendMail($mail)
    {
        /** @var BasePresenter $presenter */
        $presenter  = $this->getPresenter();
        $translator = $this->getTranslator();
        $title      = $presenter->translateMessage()->translate('userPage.management');
        $userEntity = $this->getExistUserEntity($mail);

        $latte  = new \Latte\Engine;
        $params = [
            'url'      => $presenter->link("//:Cms:Login:forgottenPassword"),
            'link' => $presenter->link("//:Cms:Login:changePassword", ['id' => $userEntity->getId(), 'code' => md5($mail)]),
        ];

        $message = new Message();
        $message->setFrom($this->emailFrom)
            ->addTo($mail)
            ->setHtmlBody($latte->renderToString(__DIR__ . "/template/forgottenPasswordEmail.latte", $params));

        $this->mailer->send($message);
        $this->logger->info("{$userEntity->getRole()} {$userEntity->getUsername()} sendMail", ['type' => LogEntity::ACTION_ACCOUNT, 'target' => $userEntity, 'action' => 'email send ok']);

//        $message = $translator->translate('user_has_been_send_email');
//        $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
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