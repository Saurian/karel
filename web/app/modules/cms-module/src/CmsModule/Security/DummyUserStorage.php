<?php
/**
 * Copyright (c) 2014 Pavel Paulík (http://www.pavelpaulik.cz)
 * DummyUserStorage
 *
 * @created 19.7.14
 * @package ${MODULE_NAME}Module
 * @author  Saurian
 */

namespace App\Security;


use Nette\Security\IIdentity;
use Nette\Security\IUserStorage;
use Nette;
use Tracy\Logger;

class DummyUserStorage implements IUserStorage
{

    protected $authenticated;


    protected $identity;


    /**
     * Sets the authenticated status of this user.
     *
     * @param  bool
     *
     * @return void
     */
    function setAuthenticated($state)
    {
        $this->authenticated = $state;
    }

    /**
     * Is this user authenticated?
     *
     * @return bool
     */
    function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Sets the user identity.
     *
     * @return void
     */
    function setIdentity(IIdentity $identity = NULL)
    {
        $this->identity = $identity;
    }

    /**
     * Returns current user identity, if any.
     *
     * @return Nette\Security\IIdentity|NULL
     */
    function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Enables log out from the persistent storage after inactivity.
     *
     * @param  string|int|\DateTime number of seconds or timestamp
     * @param                       int    Logger out when the browser is closed | Clear the identity from persistent storage?
     *
     * @return void
     */
    function setExpiration($time, $flags = 0)
    {
        // TODO: Implement setExpiration() method.
    }

    /**
     * Why was user logged out?
     *
     * @return int
     */
    function getLogoutReason()
    {
        return NULL;
    }
}