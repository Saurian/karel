<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediumData.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;


/**
 * Class MediumDataEntity
 *
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\MediaRepository")
 * @ORM\Table(name="medium_data", indexes={
 *     @ORM\Index(name="keywords_idx", columns={"keywords"}, flags={"fulltext"}),
 * })
 * @package CmsModule\Entities
 */
class MediumDataEntity
{

    use Identifier;
    use MagicAccessors;

    /**
     * @var MediumEntity
     * @ORM\ManyToOne(targetEntity="MediumEntity", inversedBy="mediaData")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $medium;


    /**
     * @var CampaignEntity
     * @ORM\ManyToOne(targetEntity="CampaignEntity", inversedBy="mediaData")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $campaign;


    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $time;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $timeType;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $identifier;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fileName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filePath;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $sound;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="text", length=65536, nullable=true, options={"comment":"fullSearch keyword"})
     */
    protected $keywords;


    /**
     * MediumDataEntity constructor.
     *
     * @param CampaignEntity $campaignEntity
     * @param MediumEntity   $mediumEntity
     */
    public function __construct(CampaignEntity $campaignEntity, MediumEntity $mediumEntity)
    {
        $this->medium   = $mediumEntity;
        $this->campaign = $campaignEntity;

        if (in_array($mediumEntity->getType(), ['image', 'url', 'zip'])) {
            $this->time     = 10;
            $this->timeType = 's';
        }

    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getTimeType()
    {
        return $this->timeType;
    }

    /**
     * @return bool
     */
    public function isSound()
    {
        return $this->sound;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }





    /**
     * @return MediumEntity
     */
    public function getMedium()
    {
        return $this->medium;
    }

    /**
     * @return CampaignEntity
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
    public function getFileName()
    {
        return $this->fileName;
    }


    /**
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     *
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }


    public function isImage()
    {
        return in_array($this->type, ['image/jpeg', 'image/png', 'image/gif'], true);
    }

    public function isVideo()
    {
        return in_array($this->type, ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'], true);
    }

    public function isZip()
    {
        return in_array($this->type, ['application/zip'], true);
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     *
     * @return $this
     */
    public function setKeywords($keywords)
    {
        if ($keywords) $this->keywords = $keywords;
        return $this;
    }



}