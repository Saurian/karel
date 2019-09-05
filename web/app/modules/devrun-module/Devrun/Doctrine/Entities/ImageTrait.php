<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    ImageTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;


/**
 * Class ImageTrait
 *
 * @package Devrun\Doctrine\Entities
 */
trait ImageTrait
{

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $systemName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $filename;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $path;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $namespace;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $width;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $height;

    /**
     * @var string
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $type;


    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $originalName
     *
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
        return $this;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }








    /**
     * Utility method, that can be replaced with `::class` since php 5.5
     *
     * @return string
     */
    public static function getImageTraitName()
    {
        return get_called_class();
    }


}