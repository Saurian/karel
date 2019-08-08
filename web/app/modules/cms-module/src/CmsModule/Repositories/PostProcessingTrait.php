<?php
/**
 * This file is part of karl.pixman.cz.
 * Copyright (c) 2018
 *
 * @file    PostProcesingTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Repositories;


trait PostProcessingTrait
{

    /**
     * Associate entity
     * no check id exist
     *
     * @param $rows
     *
     * @return array
     */
    private static function entityAssoc($rows)
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->id] = $row;
        }

        return $_rows;
    }


    public function getAssocRows($rows)
    {
        return self::entityAssoc($rows);
    }



}