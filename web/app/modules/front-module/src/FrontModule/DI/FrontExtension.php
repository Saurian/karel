<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2016
 *
 * @file    FrontExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\DI;

use Flame\Modules\Providers\IPresenterMappingProvider;
use Flame\Modules\Providers\IRouterProvider;
use FrontModule\Facades\ApiFacade;
use FrontModule\Forms\IPlayListFormFactory;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\Environment;

class FrontExtension extends CompilerExtension implements IPresenterMappingProvider, IRouterProvider
{
    const TAG_SUBSCRIBER = 'kdyby.subscriber';

    public $defaults = array(
        'publicModule' => TRUE,
        'debug'        => FALSE,

    );


    public function loadConfiguration()
    {
        parent::loadConfiguration();

        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);



        /*
         * presenters
         */


        /*
         * facades
         */
        $builder->addDefinition($this->prefix('facade.api'))
                ->setType(ApiFacade::class);



        /*
         * controls
         */
        $builder->addDefinition($this->prefix('form.playListFormFactory'))
                ->setImplement(IPlayListFormFactory::class)
                ->setInject(true);



        /*
         * subscribers
         */



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
            'Front' => 'FrontModule\*Module\Presenters\*Presenter',
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
        $lang = Environment::getConfig('lang');

        $routeList     = new RouteList();
        $routeList[]   = $frontRouter = new RouteList('Front');
        $frontRouter[] = new Route("[<locale={$lang} sk|hu|cs>/]<presenter>/<action>[/<id>]", array(
            'presenter' => array(
                Route::VALUE        => 'Homepage',
                Route::FILTER_TABLE => array(
                    'testovaci' => 'Test',
//                    'presmerovano' => 'TestRedirect',
                ),
            ),
            'action'    => array(
                Route::VALUE        => 'default',
                Route::FILTER_TABLE => array(
                    'operace-ok' => 'operationSuccess',
                ),
            ),
            'id'        => null,
            'locale'    => [
                Route::FILTER_TABLE => [
                    'cz'  => 'cs',
                    'sk'  => 'sk',
                    'pl'  => 'pl',
                    'com' => 'en'
                ]]
        ));
        return $routeList;

    }


}