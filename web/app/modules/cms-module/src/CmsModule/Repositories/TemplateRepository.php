<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    TemplateRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;

use CmsModule\Repositories\Queries\TemplateQuery;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

class TemplateRepository extends EntityRepository
{


    public function getUserAllowedQuery(User $user)
    {
        $query = new TemplateQuery();
        if (!$user->isAllowed('Cms:Campaign', 'listAllTemplates')) {
            $query->byUser($user);
        }

        return $query;
    }


    /**
     * @return array [id => name, id => name ...]
     */
    public function getTemplates(User $user)
    {
        $query = $this->getUserAllowedQuery($user);

        $templates = [];
        foreach ($templateEntities = $this->fetch($query) as $item) {
            $templates[$item->id] = $item->name;
        }

        return $templates;
    }


}