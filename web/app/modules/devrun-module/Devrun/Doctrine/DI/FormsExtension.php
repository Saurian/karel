<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    FormExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Events\DI\EventsExtension;
use Nette;

class FormsExtension extends Nette\DI\CompilerExtension implements IEntityProvider
{


    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('entityFormMapper'))
            ->setFactory('Devrun\Doctrine\DoctrineForms\EntityFormMapper');


        // tree
        $builder->addDefinition($this->prefix('listener.treeListener'))
            ->setType('Gedmo\Tree\TreeListener')
            ->addSetup('setAnnotationReader', ['@Doctrine\Common\Annotations\Reader'])
            ->addTag(EventsExtension::TAG_SUBSCRIBER);

        // translatable
        $builder->addDefinition($this->prefix('listener.translatableListener'))
            ->setType('Gedmo\Translatable\TranslatableListener')
            ->addTag(EventsExtension::TAG_SUBSCRIBER);


    }


    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
            $compiler->addExtension('doctrineForms', new FormsExtension());
        };
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    public function getEntityMappings()
    {
        return array(
//            'Devrun\Doctrine' => dirname(__DIR__) . '/Entities/',
        );
    }
}