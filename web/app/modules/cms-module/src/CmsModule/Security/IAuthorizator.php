<?php
/**
 * This file is part of the smart-up
 * Copyright (c) 2016
 *
 * @file    IAuthorizator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Security;


use FrontModule\Entities\ProjectsEntity;

interface IAuthorizator
{


    /**
     * set allow
     *
     * @return mixed
     */
    public function setAllow();


}