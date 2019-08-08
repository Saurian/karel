<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    DateTimeTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;


use Nette\Utils\DateTime;

trait DateTimeTrait
{

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $inserted;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @return DateTime
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * @param DateTime $inserted
     */
    public function setInserted($inserted)
    {
        $this->inserted = $inserted;
    }

    /**
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Utility method, that can be replaced with `::class` since php 5.5
     *
     * @return string
     */
    public static function getDateTimeTraitName()
    {
        return get_called_class();
    }


}