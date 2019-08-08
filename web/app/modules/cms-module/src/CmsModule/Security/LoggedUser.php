<?php
/**
 * This file is part of smart-up.
 * Copyright (c) 2018
 *
 * @file    LoggedUser.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Security;

use CmsModule\Entities\UserEntity;
use CmsModule\Repositories\UserRepository;
use Nette\Security\User;

class LoggedUser
{

    /** @var User */
    private $user;

    /** @var UserEntity */
    private $userEntity;

    /** @var UserRepository */
    private $userRepository;

    /**
     * LoggedUser constructor.
     *
     * @param User           $user
     * @param UserRepository $userRepository
     */
    public function __construct(User $user, UserRepository $userRepository)
    {
        $this->user           = $user;
        $this->userRepository = $userRepository;
    }


    /**
     * @return UserEntity
     */
    public function getUserEntity()
    {
        if ($this->userEntity === NULL) {

            if ($this->user->isLoggedIn()) {
                $this->userEntity = $this->userRepository->find($this->user->getId());
            }
        }

        return $this->userEntity;

    }

    /**
     */
    public function clearUserEntity()
    {
        $this->userEntity = null;
    }


}