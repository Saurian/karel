<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    UuidV4EntityTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Pavel Paulik
 */
trait IdentifiedEntityTrait
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;


    /**
     * @return integer
     */
    final public function getId()
    {
        return $this->id;
    }


    public function __clone()
    {
        $this->id = NULL;
    }


}
