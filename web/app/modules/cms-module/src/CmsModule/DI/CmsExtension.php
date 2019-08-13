<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    FrontExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\DI;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceLogEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\LogEntity;
use CmsModule\Entities\MediumDataEntity;
use CmsModule\Entities\TemplateEntity;
use CmsModule\Listeners\MediaDataListener;
use Flame\Modules\Providers\IPresenterMappingProvider;
use Flame\Modules\Providers\IRouterProvider;
use CmsModule\Entities\UserEntity;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Doctrine\DI\OrmExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;


class CmsExtension extends CompilerExtension implements IPresenterMappingProvider, IRouterProvider, IEntityProvider
{

    public $defaults = array(
        'paths' => [
            'webTempDir'     => '%wwwDir%/webtemp',
        ],

        'newPassword'  => 123123,
        'mediaDir'     => 'media',
        'dataPath'     => '%wwwDir%',
        'mediaPath'    => '%wwwDir%/media',
        'emailSending' => true,
        'emailFrom'    => 'Karl von Bahnhof <info@cms.pixatori.com>',
    );


    public function loadConfiguration()
    {
        parent::loadConfiguration();

        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);


        /*
         * repositories
         */
        $builder->addDefinition($this->prefix('repository.media'))
            ->setFactory('CmsModule\Repositories\MediaRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, MediumDataEntity::class)
            ->setInject(true);

        $builder->addDefinition($this->prefix('repository.template'))
            ->setFactory('CmsModule\Repositories\TemplateRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, TemplateEntity::class);

        $builder->addDefinition($this->prefix('repository.device'))
            ->setFactory('CmsModule\Repositories\DeviceRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, DeviceEntity::class)
            ->setInject(true);

        $builder->addDefinition($this->prefix('repository.deviceGroup'))
            ->setFactory('CmsModule\Repositories\DeviceGroupRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, DeviceGroupEntity::class)
            ->setInject(true);

        $builder->addDefinition($this->prefix('repository.campaign'))
            ->setFactory('CmsModule\Repositories\CampaignRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, CampaignEntity::class)
            ->setInject(true);

        $builder->addDefinition($this->prefix('repository.user'))
            ->setFactory('CmsModule\Repositories\UserRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, UserEntity::class)
            ->setInject(true);

        $builder->addDefinition($this->prefix('repository.log'))
            ->setFactory('CmsModule\Repositories\LogRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, LogEntity::class);

        $builder->addDefinition($this->prefix('repository.deviceLog'))
            ->setFactory('CmsModule\Repositories\DeviceLogRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, DeviceLogEntity::class);


        /*
         * facades
         */
        $builder->addDefinition($this->prefix('facade.campaign'))
            ->setFactory('CmsModule\Facades\CampaignFacade');

        $builder->addDefinition($this->prefix('facade.device'))
            ->setFactory('CmsModule\Facades\DeviceFacade');

        $builder->addDefinition($this->prefix('facade.user'))
            ->setFactory('CmsModule\Facades\UserFacade');

        $builder->addDefinition($this->prefix('facade.mediaData'))
            ->setFactory('CmsModule\Facades\MediaDataFacade', [$config['mediaPath'], $config['mediaDir'], ]);

        $builder->addDefinition($this->prefix('facade.deviceLog'))
            ->setFactory('CmsModule\Facades\DeviceLogFacade');


//        $builder->addDefinition($this->prefix('control.environment'))
//            ->setImplement('CmsModule\Control\IJSEnvironmentControl')
//            ->setInject();


        /*
         * presenters
         */


        /*
         * controls
         */
        $builder->addDefinition($this->prefix('control.deviceControlFactory'))
            ->setImplement('CmsModule\Controls\IDeviceControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.devicesControlFactory'))
            ->setImplement('CmsModule\Controls\IDevicesControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.campaignsControlFactory'))
            ->setImplement('CmsModule\Controls\ICampaignsControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.templateControlFactory'))
            ->setImplement('CmsModule\Controls\ITemplateControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.campaignsFilterControlFactory'))
            ->setImplement('CmsModule\Controls\ICampaignsFilterControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.campaignFilterTagsControlFactory'))
            ->setImplement('CmsModule\Controls\ICampaignFilterTagsControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.flashMessageControlFactory'))
            ->setImplement('CmsModule\Controls\IFlashMessageControlFactory');


        /*
         * forms
         */
        $builder->addDefinition($this->prefix('form.loginForm'))
            ->setImplement('CmsModule\Forms\ILoginFormFactory')
            ->setInject(true);


        $builder->addDefinition($this->prefix('form.deviceForm'))
            ->setImplement('CmsModule\Forms\IDeviceFormFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.deviceGroupForm'))
            ->setImplement('CmsModule\Forms\IDeviceGroupFormFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.campaignForm'))
            ->setImplement('CmsModule\Forms\ICampaignFormFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.templateForm'))
            ->setImplement('CmsModule\Forms\ITemplateFormFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.adminTemplateForm'))
            ->setImplement('CmsModule\Forms\IAdminTemplateFormFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.userForm'))
            ->setImplement('CmsModule\Forms\IUserFormFactory')
            ->addSetup('setEmailSending', ['emailSending' => $config['emailSending']])
            ->addSetup('setEmailFrom', ['emailFrom' => $config['emailFrom']])
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.registrationForm'))
            ->setImplement('CmsModule\Forms\IRegistrationFormFactory')
            ->addSetup('setEmailSending', ['emailSending' => $config['emailSending']])
            ->addSetup('setEmailFrom', ['emailFrom' => $config['emailFrom']])
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.forgottenPasswordForm'))
            ->setImplement('CmsModule\Forms\IForgottenPasswordFormFactory')
            ->addSetup('setEmailSending', ['emailSending' => $config['emailSending']])
            ->addSetup('setEmailFrom', ['emailFrom' => $config['emailFrom']])
            ->setInject(true);

        $builder->addDefinition($this->prefix('form.changePasswordForm'))
            ->setImplement('CmsModule\Forms\IChangePasswordFormFactory')
            ->addSetup('setEmailSending', ['emailSending' => $config['emailSending']])
            ->addSetup('setEmailFrom', ['emailFrom' => $config['emailFrom']])
            ->setInject(true);


        /*
         * system
         */
        $builder->addDefinition($this->prefix('authorizator'))
            ->setFactory('CmsModule\Security\Authorizator');

        $builder->addDefinition($this->prefix('authenticator'))
            ->setFactory('CmsModule\Security\Authenticator');

        $builder->addDefinition($this->prefix('user.logged'))
            ->setFactory('CmsModule\Security\LoggedUser');


//        $builder->addDefinition($this->prefix('user.logged'))
//            ->setFactory('CmsModule\Security\LoggedUser');

        /*
         * presenters
         */
        $builder->addDefinition($this->prefix('presenter.user'))
            ->setFactory('CmsModule\Presenters\UsersPresenter')
            ->addSetup('injectNewPassword', ['password' => $config['newPassword']]);


        // subscribers


        // tree
        $builder->addDefinition($this->prefix('listener.sortableListener'))
            ->setType('Gedmo\Sortable\SortableListener')
            ->addSetup('setAnnotationReader', ['@Doctrine\Common\Annotations\Reader'])
            ->addTag(EventsExtension::TAG_SUBSCRIBER);


        /*
         * Listeners
         */
        // user
        $builder->addDefinition($this->prefix('listener.blabeableListener'))
            ->setFactory('Devrun\Doctrine\Listeners\BlameableListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // time
        $builder->addDefinition($this->prefix('listener.timeStableListener'))
            ->setFactory('Devrun\Doctrine\Listeners\TimeStableListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // position
        $builder->addDefinition($this->prefix('listener.positionListener'))
            ->setFactory('Devrun\Doctrine\Listeners\PositionListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // log
        $builder->addDefinition($this->prefix('listener.logListener'))
            ->setFactory('CmsModule\Listeners\LogListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        $builder->addDefinition($this->prefix('listener.mediaDataListener'))
            ->setFactory(MediaDataListener::class)
            ->setArguments([$config['dataPath']])
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // identity
        $builder->addDefinition($this->prefix('listener.identityListener'))
            ->setFactory('CmsModule\Listeners\IdentityListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);


    }


    public function beforeCompile()
    {
        $config = $this->getConfig($this->defaults);

        foreach ($config['paths'] as $pathSystem) {
            if (!is_dir($pathSystem)) {
                mkdir($pathSystem, 0777, true);
            }
        }

        parent::beforeCompile();
    }


    /**
     * Returns array of ClassNameMask => PresenterNameMask
     *
     * @example return array('*' => 'Booking\*Module\Presenters\*Presenter');
     * @return array
     */
    public function getPresenterMapping()
    {
        return array(
            'Cms' => 'CmsModule\*Module\Presenters\*Presenter',
        );
    }

    /**
     * Returns array of ServiceDefinition,
     * that will be appended to setup of router service
     *
     * @example https://github.com/nette/sandbox/blob/master/app/router/RouterFactory.php - createRouter()
     * @return \Nette\Application\IRouter
     */
    public function getRoutesDefinition()
    {
        $router   = new RouteList();
        $router[] = $adminRouter = new RouteList('Cms');

        $cmsDefaultLang = $defaultLocale = $availableLocales = 'cs';

        if ($translation = \Nette\Environment::getService('translation.default')) {
            $availableLocalesArray = ($locales = $translation->getAvailableLocales())
                ? $locales
                : [$cmsDefaultLang];

            $availableLocales = implode('|', array_unique(preg_replace("/^(\w{2})_(.*)$/m", "$1", $availableLocalesArray)));

            if ($default = $translation->getDefaultLocale()) $defaultLocale = $default;
        }

        $adminRouter[] = new Route("[<module>-]admin/[<locale=$defaultLocale $availableLocales>/]<presenter>/<action>[/<id>]", array(
            'presenter' => array(
                Route::VALUE        => 'Campaign',
                Route::FILTER_TABLE => array(
                    'zarizeni'         => 'Device',
                    'skupiny-zarizeni' => 'DeviceGroup',
                    'kampane'          => 'Campaign',
                    'uzivatele'        => 'Users',
                    'prihlasit-se'     => 'Login',
                    'sablony'          => 'Template',
                    'nastaveni'        => 'Settings',
                    'statistiky'       => 'Statistic',
                ),
            ),
            'action'    => [
                Route::VALUE        => 'default',
                Route::FILTER_TABLE => array(
                    'novy-uzivatel'    => 'newUser',
                    'zapomenute-heslo' => 'forgottenPassword',
                    'zmena-hesla' => 'changePassword',
                ),
            ]
        ));

        return $router;
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return array(
            'CmsModule\Entities' => dirname(__DIR__) . '/Entities/',
        );
    }
}