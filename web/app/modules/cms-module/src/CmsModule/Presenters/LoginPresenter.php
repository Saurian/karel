<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    LoginPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Presenters;

use CmsModule\Controls\FlashMessageControl;
use CmsModule\Entities\UserEntity;
use CmsModule\Facades\DeviceFacade;
use CmsModule\Facades\UserFacade;
use CmsModule\Forms\ChangePasswordForm;
use CmsModule\Forms\ForgottenPasswordForm;
use CmsModule\Forms\IForgottenPasswordFormFactory;
use CmsModule\Forms\ILoginFormFactory;
use CmsModule\Forms\IRegistrationFormFactory;
use CmsModule\Forms\LoginForm;
use CmsModule\Forms\RegistrationForm;
use Nette\Environment;

class LoginPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    /** @var UserFacade @inject */
    public $userFacade;

    /** @var DeviceFacade @inject */
    public $deviceFacade;

    /** @var ILoginFormFactory @inject */
    public $loginFormFactory;

    /** @var IRegistrationFormFactory @inject */
    public $registrationFormFactory;

    /** @var IForgottenPasswordFormFactory @inject */
    public $forgottenPasswordFormFactory;

    /** @var UserEntity */
    private $changePasswordUserEntity;


    public function actionChangePassword($id, $code)
    {
        /** @var UserEntity $userEntity */
        if (!$userEntity = $this->userRepository->find($id)) {
            $title = $this->translator->translate("messages.loginPage.forgottenUserTitle");
            $this->flashMessage($this->translator->translate("messages.loginPage.userNotFound"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect(":Cms:Login:");
        }

        if ($userEntity->getNewPassword() != $code) {
            $title = $this->translator->translate("messages.loginPage.forgottenUserTitle");
            $this->flashMessage($this->translator->translate("messages.loginPage.userCodeNotValid"), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect(":Cms:Login:forgottenPassword");
        }

        $this->changePasswordUserEntity = $userEntity;
    }


    public function actionDefault($code = null)
    {

    }


    /**
     * @param $name
     *
     * @return LoginForm
     */
    protected function createComponentLoginForm($name)
    {
        $form = $this->loginFormFactory->create();
        $form->setTranslator($this->translator->domain("messages.forms.$name"));


        $form
            ->create()
            ->bootstrap3Render();

        if ($code = $this->getParameter('code')) {
            $form->setCode($code);
            if ($userEntity = $this->userRepository->findOneBy(['password' => $code])) {
                $form->bindEntity($userEntity);
            }
        }

        $form->onError[] = function (LoginForm $form) {
            /*if (Environment::isConsole()) {
                dump($form->getErrors());
            }*/
        };

        $form->onSuccess[] = function (LoginForm $form, $values) {

            $this->flashMessage("Přihlášen", 'success');

            $this->restoreRequest($this->backlink);
            $this->ajaxRedirect(":Cms:Campaign:");
        };

        return $form;
    }


    /**
     * @param $name
     *
     * @return RegistrationForm
     */
    protected function createComponentRegistrationForm($name)
    {
        $form = $this->registrationFormFactory->create();
        $form->setTranslator($this->translator->domain("messages.forms.$name"));

        $form->create()
            ->bootstrap3Render()
            ->bindEntity(new UserEntity(null, null, null, null, null))
            ->onSuccess[] = function (RegistrationForm $form, $values) use ($name) {

            /** @var UserEntity $entity */
            $entity = $form->getEntity();
            $entity
                ->setRole(UserEntity::ADMIN)
                ->setUsername($values->mail)
                ->setPassword($values->password);

            $this->userFacade->createNewUserGroupForUser($entity, $values->group);

            $rootDeviceGroupEntity = $this->deviceFacade->createNewDeviceGroupForUser($entity);
            $rootDeviceGroupEntity
                ->setCreatedBy($entity)
                ->setUpdatedBy($entity);

            $em = $form->getEntityMapper()->getEntityManager();
            $em->persist($entity)->flush();

            $title = $this->translator->translate("messages.loginPage.user_added_title");
            $this->flashMessage($this->translator->translate("messages.loginPage.user_added", null, ['name' => $entity->getUsername()]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_INFO);
            $this->ajaxRedirect('default');
        };

        $form->onError[] = function (RegistrationForm $form) {

            if (Environment::isConsole()) {
                dump($form->getErrors());
            }

        };

        return $form;
    }


    protected function createComponentForgottenPasswordForm($name)
    {
        $form = $this->forgottenPasswordFormFactory->create();
        $form->setTranslator($this->translator->domain("messages.forms.$name"));

        $form->create()
            ->bootstrap3Render()
            ->bindEntity(new UserEntity(null, null, null, null, null))
            ->onSuccess[] = function (ForgottenPasswordForm $form, $values) {

            /** @var UserEntity $existUserEntity */
            if ($existUserEntity = $this->userRepository->findOneBy(['mail' => $mail = $values->mail])) {
                $existUserEntity->setNewPassword($values->mail);

                $em = $form->getEntityMapper()->getEntityManager();
                $em->persist($existUserEntity)->flush();

            }

            $title = $this->translator->translate("messages.loginPage.forgottenUserTitle");
            $this->flashMessage($this->translator->translate("messages.loginPage.userCodeSend", null, ['mail' => $mail]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->ajaxRedirect('default');
        };

        return $form;
    }


    protected function createComponentChangePasswordForm($name)
    {
        $form = $this->changePasswordFormFactory->create();
        $form->setTranslator($this->translator->domain("messages.forms.$name"));

        $form->create()
            ->bootstrap3Render()
            ->bindEntity($this->changePasswordUserEntity)
            ->onSuccess[] = function (ChangePasswordForm $form, $values) {

            /** @var UserEntity $entity */
            $entity = $form->getEntity();
            $entity->setPassword($values->password)->resetNewPassword();

            $em = $form->getEntityMapper()->getEntityManager();
            $em->persist($entity)->flush();


            $title = $this->translator->translate("messages.loginPage.forgottenUserTitle");
            $this->flashMessage($this->translator->translate("messages.loginPage.userPasswordChange", null, ['mail' => $entity->getMail()]), FlashMessageControl::TOAST_TYPE, $title, FlashMessageControl::TOAST_SUCCESS);
            $this->ajaxRedirect('default');
        };

        return $form;
    }


}