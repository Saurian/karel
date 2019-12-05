<?php


namespace CmsModule\Repositories;

use CmsModule\Repositories\Queries\CalendarQuery;
use Kdyby\Doctrine\EntityRepository;

class CalendarRepository extends EntityRepository
{

    public function getQuery()
    {
        $query = (new CalendarQuery());

        return $query;
    }



}