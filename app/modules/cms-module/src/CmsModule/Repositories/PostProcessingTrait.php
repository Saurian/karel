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
     * @param string $primaryKey
     * @return array
     */
    public function getAssoc($rows, $primaryKey = 'id')
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->$primaryKey] = $row;
        }

        return $_rows;
    }


    /**
     * Pairs by key entity
     * no check id exist
     *
     * @param $rows
     * @param string $primaryKey
     * @param string $columnValue
     * @return array
     */
    public function getPairs($rows, $primaryKey = 'id', $columnValue = 'name')
    {
        $_rows = [];
        foreach ($rows as $row) {
            $_rows[$row->$primaryKey] = $row->$columnValue;
        }

        return $_rows;
    }





}