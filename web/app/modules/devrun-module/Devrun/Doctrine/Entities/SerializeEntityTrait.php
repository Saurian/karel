<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    SerializeEntityTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;

trait SerializeEntityTrait
{

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=32, options={"fixed" = true})
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="CmsModule\Doctrine\Id\SerializeGenerator")
     */
    protected $id;


    /**
     * @param bool $regenerate
     *
     * @return string
     */
    public function getId($regenerate = false)
    {
        if ($regenerate) {
            $this->id = md5(serialize($this));

        } else {
            if ($this->id === NULL) {
                $this->id = md5(serialize($this));
            }
        }
        return $this->id;
    }

}