<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    PositionTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;

use Gedmo\Mapping\Annotation as Gedmo;

trait PositionTrait
{


    /**
     * @var integer
     * __@__Gedmo\SortablePosition()
     * @Gedmo\Sortable(groups={"category"})
     * @ORM\Column(type="integer")
     */
    protected $position;


    /**
     * @ORM\Column(length=128)
     */
    protected $category = '';


    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return PositionTrait
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }






    /**
     * Utility method, that can be replaced with `::class` since php 5.5
     *
     * @return string
     */
    public static function getPositionTraitName()
    {
        return get_called_class();
    }


}