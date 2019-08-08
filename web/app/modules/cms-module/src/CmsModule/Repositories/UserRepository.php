<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    UserRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Repositories\Queries\UserQuery;
use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

class UserRepository extends EntityRepository implements IFilter
{

    const SESSION_NAME = 'userFilter';

    use FilterRepositoryTrait;


    public function getActiveRowsCount()
    {
        $query = (new UserQuery())->isActive();
        return $this->fetch($query)->getTotalCount();
    }


    public function getNonActiveRowsCount()
    {
        $query = (new UserQuery())->isNotActive();
        return $this->fetch($query)->getTotalCount();
    }

    public function getAllRowsCount()
    {
        $query = (new UserQuery());
        return $this->fetch($query)->getTotalCount();
    }

    public function getAssignedDevices(User $user)
    {
        $userEntity = $this->createQueryBuilder('e')
            ->addSelect('d')
            ->leftJoin('e.devices', 'd')
            ->where('e.id = :id')->setParameter('id', $user->getId())
//            ->andWhere('d.active = true')
//            ->andWhere('d.deviceGroup IS NULL')
            ->getQuery()
            ->getOneOrNullResult();

//        $query = (new UserQuery());
//        $query
//            ->withDevices()
//            ->byDevices($user);


//        $q  = $this->fetch($query);
//        dump($q->getIterator());

//        dump($userEntity);
//        die();


        return $userEntity ? $userEntity->devices : [];
    }



    public function getAssignedDevicesGroups(User $user)
    {
        $userEntity = $this->createQueryBuilder('e')
            ->addSelect('g')
            ->leftJoin('e.devicesGroups', 'g')
            ->where('e.id = :id')->setParameter('id', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();

        return $userEntity ? $userEntity->devicesGroups->toArray() : [];
    }




    /**
     * @param $userName
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByLogin($userName)
    {
        return $this->createQueryBuilder('e')
            ->where("e.username = :username")
            ->setParameter('username', $userName)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_ARRAY);
    }


    public function getUserAllowedQuery(User $user)
    {
        $query = (new UserQuery());

        if (!$user->isAllowed('Cms:Users', 'listAllUsers')) {
            // list only logged user
            if ($user->isAllowed('Cms:Users', 'listThisUsers')) {
//                $query->byUser($user);
            }
            // list all users by created by our user
            if ($user->isAllowed('Cms:Users', 'listCreatedByUser')) {
//                $query->withCreatedByUser($user);
            }
            // list all users by our logged user devices and groups set
            if ($user->isAllowed('Cms:Users', 'listByDevicesUser')) {
                $query
//                    ->withDevices()
//                    ->byDevices($user);
                    ->byTest($user);
            }
        }

        return $query;
    }



}