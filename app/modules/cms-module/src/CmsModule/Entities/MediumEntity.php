<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediumEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class MediumEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="medium")
 * @package CmsModule\Entities
 */
class MediumEntity
{

    use Identifier;
    use DateTimeTrait;
    use BlameableTrait;
    use MagicAccessors;


    /**
     * @var TemplateEntity
     * @ORM\ManyToOne(targetEntity="TemplateEntity", inversedBy="media")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $template;

    /**
     * @var MediumDataEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MediumDataEntity", mappedBy="medium")
     */
    protected $mediaData;



    /**
     * @var string [image,video,url,zip]
     * @ORM\Column(type="string")
     */
    protected $type;




    /**
     * MediumEntity constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }


    /**
     * @param TemplateEntity $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }




}