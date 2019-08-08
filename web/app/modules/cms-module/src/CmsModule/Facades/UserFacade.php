<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UserFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;

use CmsModule\Entities\UserEntity;
use CmsModule\Repositories\UserRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\SmartObject;

/**
 * Class UserFacade
 *
 * @package CmsModule\Facades
 * @method onActivate(UserEntity $userEntity)
 */
class UserFacade
{
    use SmartObject;

    use PositionableTrait;

    use DeviceFacadeTrait;

    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManager */
    private $em;

    /** @var Callback[] */
    public $onActivate = [];


    /**
     * UserFacade constructor.
     *
     * @param UserRepository $userRepository
     * @param EntityManager  $em
     */
    public function __construct(UserRepository $userRepository, EntityManager $em)
    {
        $this->userRepository = $userRepository;
        $this->em             = $em;
    }


    public function setActive(UserEntity $userEntity, $active)
    {
        $userEntity->setActive($active);
        $this->em->persist($userEntity);

        $this->onActivate($userEntity);
        $this->em->flush();
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }


    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->userRepository;
    }


}