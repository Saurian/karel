<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    FilterRepositoryTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use Nette\Http\Session;

trait FilterRepositoryTrait
{

    /**
     * @var Session
     */
    private $session;



    public function inject(Session $session)
    {
        $this->session = $session;
    }


    /**
     * @return \Nette\Http\SessionSection
     */
    private function getSection()
    {
        return $this->session->getSection(self::SESSION_NAME);
    }


    public function existFilterActive()
    {
        $section = $this->getSection();
        return isset($section->active);
    }

    public function getFilterActive()
    {
        $section = $this->getSection();
        return $section->active;
    }

    public function setFilterActive($active)
    {
        $section = $this->getSection();
        $section->active = $active;
    }

    public function clearFilter()
    {
        $section = $this->getSection();
        unset($section->active);
    }


}