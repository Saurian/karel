<?php
/**
 * This file is part of the vanocni_soutez
 * Copyright (c) 2014
 *
 * @file    Authorizator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Security;

use CmsModule\Forms\CampaignForm;
use CmsModule\Forms\DeviceForm;
use CmsModule\Forms\TargetGroupForm;
use CmsModule\Forms\UserForm;
use CmsModule\Repositories\UserRepository;
use Nette\Security\Permission;
use Nette\Security\User;

class Authorizator extends Permission
{

    const EDIT_ROLE_RESOURCE = User::class . ":editRole";


    /** @var User */
    private $user;

    /** @var UserRepository */
    private $userRepository;


    /**
     * Authorizator constructor.
     *
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;

        // roles
        $this->addRole('guest');                // host | nepřihlášený
        $this->addRole('watcher', 'guest');     // pozorovatel
        $this->addRole('editor', 'watcher');    // editor (běžný uživatel)
        $this->addRole('master');               // mistr
        $this->addRole('admin', 'master');      // správce
        $this->addRole('supervisor', 'admin');  // vývojář

        // resources
        $this->addResource('Front:Homepage');
        $this->addResource('Cms:Error4xx');
        $this->addResource('Cms:Login');
        $this->addResource('Cms:Dashboard');
        $this->addResource('Cms:Campaign');
        $this->addResource('Cms:Device');
        $this->addResource('Cms:DeviceGroup');
        $this->addResource('Cms:Reach');
        $this->addResource('Cms:Users');
        $this->addResource('Cms:Template');
        $this->addResource('Cms:Statistic');
        $this->addResource('Cms:Api');
        $this->addResource('Cms:Settings');

        // forms resource
        $this->addResource($userFormResource = UserForm::class);
        $this->addResource($userFormResourceRole = self::EDIT_ROLE_RESOURCE);
        $this->addResource($deviceFormResource = DeviceForm::class);
        $this->addResource($campaignFormResource = CampaignForm::class);
        $this->addResource($targetGroupFormResource = TargetGroupForm::class);

        // privileges quest
        $this->deny('guest', Permission::ALL);
        $this->allow('guest', 'Cms:Login', Permission::ALL);

        // privileges watcher
        $this->allow('watcher', 'Cms:Users', ['default', 'listThisUsers', 'listByDevicesUser']);
        $this->allow('watcher', $userFormResourceRole, ['watcher']);
        $this->allow('watcher', 'Cms:Device', ['default']);
        $this->allow('watcher', 'Cms:DeviceGroup', ['default']);
        $this->allow('watcher', 'Cms:Campaign', ['default', 'calendar']);
        $this->allow('watcher', 'Cms:Error4xx', Permission::ALL);
        $this->allow('watcher', 'Cms:Reach', 'default');
        $this->deny('watcher', 'Cms:Login', 'default');

        // privileges editor
        $this->allow('editor', 'Cms:Campaign', ['nestable', 'editAssets', 'delete', 'toggleActive']);
//        $this->allow('editor', 'Cms:Device', Permission::ALL);
//        $this->allow('editor', 'Cms:DeviceGroup', Permission::ALL);
        $this->allow('editor', 'Cms:Reach', ['default', 'new', 'delete']);
        $this->deny('editor', 'Cms:Login', 'default');



        $this->allow('editor', 'Cms:Users', ['default', 'edit', 'listThisUsers', 'listByDevicesUser'   ]);
        $this->allow('editor', $userFormResource, ['selfEdit']);
        $this->allow('editor', $campaignFormResource, ['new', 'edit']);
        $this->allow('editor', $userFormResourceRole, ['watcher', 'editor']);
        $this->allow('editor', $targetGroupFormResource, ['edit']);



        $this->deny('editor', 'Cms:Users', ['listAllUsers', 'nestable']);
        $this->deny('editor', 'Cms:Device', ['listAllDevices', 'nestable']);
        $this->deny('editor', 'Cms:DeviceGroup', ['listAllDevices', 'nestable']);
        $this->deny('editor', 'Cms:Campaign', ['listAllCampaigns']);







        // privileges master
        $this->allow('master', 'Cms:Users', ['default', 'edit', 'editDevices', 'toggleActive', 'listByDevicesUser']);
        $this->deny('master', 'Cms:Users', ['listAllUsers', 'nestable']);
//        $this->allow('master', 'Cms:Users', ['changeRoleEditor', 'changeRoleWatcher',]);

        $this->allow('master', 'Cms:Campaign', ['default', 'calendar']);
        $this->allow('master', 'Cms:Device', ['default', 'nestable']);
        $this->allow('master', 'Cms:DeviceGroup', ['default', 'nestable']);
        $this->allow('master', $userFormResource, ['selfEdit']);
        $this->allow('master', $deviceFormResource, ['new', 'edit', 'delete', 'sort']);
        $this->deny('master', $campaignFormResource, ['new', 'edit']);
        $this->allow('master', $userFormResourceRole, ['watcher', 'editor', 'master']);
        $this->allow('master', $targetGroupFormResource, ['edit']);

        /*
         * privileges admin
         */
        $this->deny('admin', 'Cms:Campaign', ['listAllCampaigns']);
        $this->deny('admin', 'Cms:Device', ['listAllDevices']);
        $this->deny('admin', 'Cms:DeviceGroup', ['listAllDevices']);
        $this->deny('admin', 'Cms:Users', 'listAllUsers');
        $this->allow('admin', 'Cms:Device', ['listUsersGroup']);
        $this->allow('admin', 'Cms:Campaign', ['nestable', 'editAssets', 'delete', 'toggleActive']);
        $this->allow('admin', 'Cms:Users', ['nestable', 'listUsersGroup', 'listByDevicesUser' ]);
        $this->allow('admin', 'Cms:Reach', Permission::ALL);
        $this->allow('admin', 'Cms:Template', Permission::ALL);
        $this->allow('admin', 'Cms:Statistic', Permission::ALL);
        $this->allow('admin', 'Cms:Api', Permission::ALL);
        $this->allow('admin', 'Cms:Settings', Permission::ALL);
        $this->allow('admin', $campaignFormResource, ['new', 'edit']);
        $this->allow('admin', $userFormResource, ['newUser', 'selfEdit', 'edit', 'editRole' ]);
        $this->allow('admin', $userFormResourceRole, ['watcher', 'editor', 'master', 'admin']);


        /*
         * privileges supervisor
         */
        $this->allow('supervisor', Permission::ALL, Permission::ALL);
        $this->allow('supervisor', $campaignFormResource, ['editAllDevices']);
        $this->allow('supervisor', $deviceFormResource, ['editAllDevices']);
        $this->allow('supervisor', 'Cms:Campaign', ['listAllCampaigns', 'listAllTemplates']);
        $this->allow('supervisor', 'Cms:Users', ['listAllUsers', 'itemsDetail', 'login']);
        $this->deny('supervisor', 'Cms:Users', 'listCreatedByUser');
        $this->allow('supervisor', 'Cms:Device', 'listAllDevices');
        $this->allow('supervisor', 'Cms:DeviceGroup', 'listAllDevices');
        $this->deny('supervisor', 'Cms:Login', 'default');
        $this->allow('supervisor', $userFormResourceRole, ['watcher', 'editor', 'master', 'admin', 'supervisor']);

        $this->allow('supervisor', $userFormResource, ['newUser', 'selfEdit', 'edit', 'editGridRole' ]);
//        $this->deny('admin', 'Cms:Images', ['updateNamespace', 'removeNamespace!', 'removeOnlyImageNamespace!', 'delete!']);
//        $this->allow('supervisor', 'Cms:Images', ['updateNamespace', 'removeNamespace!', 'removeOnlyImageNamespace!', 'delete!']);


    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * @param UserRepository $userRepository
     *
     * @return $this
     */
    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }


}

