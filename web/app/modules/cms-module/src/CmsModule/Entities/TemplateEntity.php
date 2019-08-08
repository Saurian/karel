<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    TemplateEntity.php
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
 * Class TemplateEntity
 *
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\TemplateRepository")
 * @ORM\Table(name="template")
 * @package CmsModule\Entities
 */
class TemplateEntity
{
    use Identifier;
    use DateTimeTrait;
    use BlameableTrait;
    use MagicAccessors;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;


    /**
     * @var CampaignEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="CampaignEntity", mappedBy="template")
     */
    protected $campaigns;

    /**
     * @var MediumEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MediumEntity", mappedBy="template" ,cascade={"persist"})
     */
    protected $media;

    /**
     * @var UserEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="UserEntity", inversedBy="templates" ,cascade={"persist"})
     * @ORM\JoinTable(name="templates_users")
     */
    protected $users;


    /**
     * TemplateEntity constructor.
     */
    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
    }


    public function addMedium(MediumEntity $entity)
    {
        $this->media[] = $entity;
        $entity->template = $this;
    }


    /**
     * @return MediumEntity[]|ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return CampaignEntity[]|ArrayCollection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }




    public function updateUser(UserEntity $userEntity)
    {
        if (!$this->users->contains($userEntity)) {
            $this->users->add($userEntity);
        }
    }







}