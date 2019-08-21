<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    VersionTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;


trait VersionTrait
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     */
    protected $version;


    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }


}