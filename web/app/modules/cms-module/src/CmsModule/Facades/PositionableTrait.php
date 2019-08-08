<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    PositionableTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

trait PositionableTrait
{

    public function initPositions($indexFrom = 0)
    {
        $indexFrom--;
        $tableName = $this->getRepository()->getClassMetadata()->getTableName();

        $em = $this->getEntityManager();

        $em->getConnection()->exec("SET @r=$indexFrom;");
        $em->getConnection()->exec("UPDATE $tableName SET POSITION = @r:= (@r+1) ORDER BY ID ASC;");
    }


    /**
     * @return EntityRepository
     */
    abstract public function getRepository();


    /**
     * @return EntityManager
     */
    abstract public function getEntityManager();


}
