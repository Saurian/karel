<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    LoginForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Forms;

use CmsModule\Entities\UserEntity;
use Nette\Application\UI\Form;

interface ILoginFormFactory
{
    /** @return LoginForm */
    function create();
}

/**
 * Class LoginForm
 *
 * @package CmsModule\Forms
 * @method onLoggedIn($form, $user)
 */
class LoginForm extends BaseForm
{

    protected $autoButtonClass = false;

    protected $labelControlClass = 'div class="hidden"';

    protected $controlClass = 'div class=col-sm-12';

    /** @var array of event */
    public $onLoggedIn = [];

    private $code;


    /** @return LoginForm */
    public function create()
    {
        // $this->addProtection('Vypršela platnost zabezpečovacího tokenu. Prosím, odešlete přihlašovací formulář znovu.');

        $this->addText('username')
            ->setAttribute('placeholder', "placeholder.username")
            ->addRule(Form::FILLED, 'ruleUsername')
            ->addRule(Form::MIN_LENGTH, 'ruleMinLength', 4)
            ->addRule(Form::MAX_LENGTH, 'ruleMaxLength', 32);

        $this->addPassword('password')
            ->setAttribute('placeholder', "placeholder.password")
            ->addRule(Form::FILLED, 'rulePassword');

        $this->addCheckbox('remember', 'remember')->getControl()->class[] = 'icheck';
        $this->addSubmit('send', 'login')->setAttribute('class', 'btn btn-lg btn-info btn-block text-uppercase');
        $this->onSuccess[] = array($this, 'formSuccess');

        $this->getElementPrototype()->class = 'margin-bottom-0';

        return $this;
    }


    /**
     * @param LoginForm $form
     */
    public function formSuccess(LoginForm $form)
    {
        $presenter = $this->getPresenter();

        try {
            $values = $form->getValues();
            $user = $presenter->getUser();

            if ($values['remember']) {
                $user->setExpiration(0);
                // $user->setExpiration('14 days', IUserStorage::CLEAR_IDENTITY);
            }

            if ($this->code) {
                /** @var UserEntity $entity */
                $entity = $form->getEntity();

                if ($entity->getPassword() == $this->code && !$entity->isActive()) {
                    $entity->setActive(true);
                    $this->getEntityMapper()->getEntityManager()->persist($entity)->flush();
                }
            }

            $user->login($values['username'], $values['password']);
            $this->onLoggedIn($this, $user);

        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }

    }



    /**
     * @param mixed $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }



}