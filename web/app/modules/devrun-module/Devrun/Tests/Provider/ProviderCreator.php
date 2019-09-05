<?php
/**
 * This file is part of the nova.superletuska.cz
 * Copyright (c) 2016
 *
 * @file    ProviderCreator.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Tests\Provider;

class ProviderCreator
{

    private $providerData = [];

    /**
     * ProviderCreator constructor.
     *
     * @param array $originalData
     */
    public function __construct(array $originalData)
    {
        $this->setProviderData($originalData);
    }

    /**
     * @param array $providerData
     */
    private function setProviderData($providerData)
    {
        $this->providerData = $providerData;



    }

    /**
     * @return array
     */
    public function getProviderData()
    {

        return $this->providerData;
    }






}

class FallbackValue
{
    /** @var */
    private $valid;

    /** @var array */
    private $invalid = [];

    /**
     * FallbackValue constructor.
     *
     * @param       $valid
     * @param array $invalid
     */
    public function __construct($valid, array $invalid)
    {
        $this->valid   = $valid;
        $this->invalid = $invalid;
    }


}