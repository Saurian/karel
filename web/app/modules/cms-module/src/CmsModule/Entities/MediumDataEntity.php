<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediumData.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\PositionTrait;
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

    const TIME_PATTERN = "%^(\d+)\s*(\w+)$%";

    use Identifier;
    use PositionTrait;
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
     * @var string [20 minutes|10 seconds]
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $time;


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

        if (in_array($mediumEntity->getType(), MediumEntity::getTypes())) {
            $this->time = "10 seconds";
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
     * 20 seconds
     *
     * @return int from pattern [20 seconds]
     */
    public function getTimeNumeric(): int
    {
        if (preg_match("%^(\d+)\s*(\w+)$%", $this->time, $matches)) {
            return $matches[1];
        }

        return 0;
    }


    /**
     * @param int $time
     * @return MediumDataEntity
     */
    public function setTimeNumeric(string $time): MediumDataEntity
    {
        if (preg_match("%^(\d+)\s*(\w+)$%", $this->time, $matches)) {
            $this->time = "$time {$matches[2]}";
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getTimeType()
    {
        if (preg_match("%^(\d+)\s*(\w+)$%", $this->time, $matches)) {
            return $matches[2];
        }

        return null;
    }


    /**
     * @param string $timeType
     * @return MediumDataEntity
     */
    public function setTimeType(string $timeType): MediumDataEntity
    {
        if (preg_match("%^(\d+)\s*(\w+)$%", $this->time, $matches)) {
            $this->time = "{$matches[1]} $timeType";
        }

        return $this;
    }


    /**
     * @param string $time
     * @return MediumDataEntity
     */
    public function setTime(string $time): MediumDataEntity
    {
        $this->time = $time;
        return $this;
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
     * @param string $url
     * @return MediumDataEntity
     */
    public function setUrl(string $url): MediumDataEntity
    {
        $this->url = $url;
        return $this;
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
        $this->keywords = trim($keywords);
        return $this;
    }



}