<?php

/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    SerializeGenerator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Id;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;

/**
 * Class SerializeGenerator
 *
 * @package CmsModule\Doctrine\Id
 * @method getId()
 */
class SerializeGenerator extends AbstractIdGenerator
{

    /**
     * Generates an identifier for an entity.
     *
     * @param EntityManager|EntityManager  $em
     * @param \Doctrine\ORM\Mapping\Entity|\Devrun\Doctrine\Entities\SerializeEntityTrait $entity
     *
     * @return mixed
     */
    public function generate(EntityManager $em, $entity)
    {
        return $entity->getId();
        //return md5(serialize($entity));
    }

    /*
    public function isPostInsertGenerator()
    {
        return true;
    }
    */


}