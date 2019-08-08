<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    CampaignEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\PositionTrait;
use Devrun\Doctrine\Entities\VersionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\DateTime;

/**
 * Class CampaignEntity
 *
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\CampaignRepository")
 * @ORM\Table(name="campaign", indexes={
 *     @ORM\Index(name="from_to_idx", columns={"realized_from", "realized_to"}),
 *     @ORM\Index(name="campaign_active_idx", columns={"active"}),
 *     @ORM\Index(name="tag_idx", columns={"tag"}),
 *     @ORM\Index(name="name_idx", columns={"name"}, flags={"fulltext"}),
 *     @ORM\Index(name="keywords_idx", columns={"keywords"}, flags={"fulltext"}),
 *     @ORM\Index(name="keywords_name_idx", columns={"name", "keywords"}, flags={"fulltext"}),
 *     @ORM\Index(name="position_idx", columns={"position"}),
 * })
 * @package CmsModule\Entities
 * @method setName($name)
 * @method getName()
 */
class CampaignEntity implements IDeviceEntity
{
    use Identifier;
    use DateTimeTrait;
    use BlameableTrait;
    use PositionTrait;
    use VersionTrait;
    use MagicAccessors;

    const DEFAULT_TAG = null;

    private static $tags = [
        'tagColor1' => 'tagColor1',
        'tagColor2' => 'tagColor2',
        'tagColor3' => 'tagColor3',
        'tagColor4' => 'tagColor4',
        'tagColor5' => 'tagColor5',
        'tagColor6' => 'tagColor6',
        'tagColor7' => 'tagColor7',
    ];


    /**
     * @var TemplateEntity
     * @ORM\ManyToOne(targetEntity="TemplateEntity", inversedBy="campaigns")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $template;

    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DeviceEntity", inversedBy="campaigns")
     * @ORM\JoinTable(name="campaigns_devices")
     */
    protected $devices;

    /**
     * @var DeviceGroupEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="DeviceGroupEntity", inversedBy="campaigns")
     * @ORM\JoinTable(name="campaigns_devices_groups")
     */
    protected $devicesGroups;

    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeviceEntity", mappedBy="defaultCampaign")
     */
    protected $defaultDevices;

    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeviceEntity", mappedBy="loopCampaign")
     */
    protected $loopDevices;

    /**
     * @var MediumDataEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MediumDataEntity", mappedBy="campaign", cascade={"persist"})
     */
    protected $mediaData;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", options={"comment" : "plán od"})
     */
    protected $realizedFrom;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", options={"comment" : "plán do"})
     */
    protected $realizedTo;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default" : false, "comment" : "aktivní"})
     */
    protected $active = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, options={"comment":"štítek"}, nullable=true)
     */
    protected $tag;

    /**
     * @var string
     * @ORM\Column(type="text", length=65536, nullable=true, options={"comment":"fullSearch keyword"})
     */
    protected $keywords;


    /**
     * CampaignEntity constructor.
     */
    public function __construct()
    {
        $this->devices = new ArrayCollection();
        $this->devicesGroups = new ArrayCollection();
        $this->defaultDevices = new ArrayCollection();
        $this->loopDevices = new ArrayCollection();
        $this->mediaData = new ArrayCollection();
    }


    /**
     * @param DateTime $realizedFrom
     *
     * @return $this
     */
    public function setRealizedFrom($realizedFrom)
    {
//        return ['12. 2. 2018 - 14. 5. 2018'];

//        if (is_string($realizedFrom)) $realizedFrom = DateTime::createFromFormat('d. m. Y', $realizedFrom);
//        if ($realizedFrom) {
//            if ($realizedFrom != $this->realizedFrom) $this->realizedFrom = $realizedFrom;
//        }
//        return $this;


        if (is_string($realizedFrom)) {
            if ($extract = explode('-', $realizedFrom)) {
                $realizedFrom = $this->formatDateTime($extract[0]);
                $realizedTo = $this->formatDateTime($extract[1]);

                $this->setRealizedTo($realizedTo);

            } else {
                $realizedFrom = DateTime::from($realizedFrom);
            }
        }
        if ($realizedFrom) {
//            $realizedFrom->setTime(0, 0, 0);
            if ($realizedFrom != $this->realizedFrom) $this->realizedFrom = $realizedFrom;
        }

        return $this;
    }


    /**
     * @param DateTime $realizedTo
     *
     * @return $this
     */
    public function setRealizedTo($realizedTo)
    {
        if (is_string($realizedTo)) {
            $realizedTo = DateTime::createFromFormat('d. m. Y', $realizedTo);
        }

        if ($realizedTo) {
//            $realizedTo->setTime(0,0,0);
            if ($realizedTo != $this->realizedTo) $this->realizedTo = $realizedTo;
        }

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRealizedFrom()
    {
        return $this->realizedFrom;
    }

    /**
     * @return DateTime
     */
    public function getRealizedTo()
    {
        return $this->realizedTo;
    }





    /**
     * @param string $tag
     *
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = in_array($tag, self::$tags) ? $tag : self::DEFAULT_TAG;
        return $this;
    }


    /**
     * @return array
     */
    public static function getTags()
    {
        return self::$tags;
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






    /**
     * @return DeviceEntity[]|ArrayCollection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * @param DeviceEntity $deviceEntity
     *
     * @return $this
     */
    public function addDevice(DeviceEntity $deviceEntity)
    {
        if (!$this->devices->contains($deviceEntity)) {
            $this->devices->add($deviceEntity);
        }

        return $this;
    }


    public function removeDevice(DeviceEntity $deviceEntity)
    {
        if ($this->devices->contains($deviceEntity)) {
            $this->devices->removeElement($deviceEntity);
        }
    }


    public function addDeviceGroup(DeviceGroupEntity $deviceGroupEntity)
    {
        if ($this->devicesGroups->contains($deviceGroupEntity)) {
            return;
        }

        $this->devicesGroups[] = $deviceGroupEntity;
    }


    public function removeDeviceGroup(DeviceGroupEntity $deviceGroupEntity)
    {
        if ($this->devicesGroups->contains($deviceGroupEntity)) {
            $this->devicesGroups->removeElement($deviceGroupEntity);
        }
    }



    /**
     * @return MediumDataEntity[]|ArrayCollection
     */
    public function getMediaData()
    {
        return $this->mediaData;
    }



    public function addMediumData(MediumDataEntity $mediumDataEntity)
    {
        if ($this->mediaData->contains($mediumDataEntity)) {
            return;
        }

        $this->mediaData[] = $mediumDataEntity;
    }

    public function removeMediumData(MediumDataEntity $mediumDataEntity)
    {
        if ($this->mediaData->contains($mediumDataEntity)) {
            $this->mediaData->removeElement($mediumDataEntity);
        }
    }


    public function synchronizeMediaDataFromTemplate()
    {
        if ($template = $this->template) {

            /*
             * template media must be set
             */
            if (($media = $template->getMedia()) && !$template->getMedia()->isEmpty()) {

                $mediaData = $this->mediaData;

                foreach ($mediaData as $mediumDataEntity) {

                    /*
                     * remove mediumData if not set in template
                     */
                    if (!$media->contains($mediumDataEntity->getMedium())) {
                        $mediaData->removeElement($mediumDataEntity);
                    }
                }

                /*
                 * add new mediumData if mediumData->template = $template->medium not set
                 */
                foreach ($media as $mediumEntity) {

                    $contain = false;
                    foreach ($mediaData as $mediumDataEntity) {
                        if ($mediumEntity->id == $mediumDataEntity->getMedium()->id) {
                            $contain = true;
                            break;
                        }
                    }

                    if (!$contain) {
                        $this->addMediumData($newMediumData = new MediumDataEntity($this, $mediumEntity));
                    }
                }

            } else {

                /*
                 * template media is empty, must be empty mediaData too
                 */
                $this->mediaData->clear();
            }
        }
    }


    /**
     * @return DeviceGroupEntity[]|ArrayCollection
     */
    public function getDevicesGroups()
    {
        return $this->devicesGroups;
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
     * @return TemplateEntity
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        if (is_string($active)) $active = json_decode($active);
        $this->active = (bool)$active;
        return $this;
    }













    /**
     * @param $datetime
     *
     * @return DateTime|mixed
     */
    private function formatDateTime($datetime)
    {
        if (is_string($datetime)) {
            $datetime = str_replace(' ', '', $datetime);

            if (preg_match('/\d+.\d+.\d{4}/', $datetime)) {
                $formatDatetime = DateTime::createFromFormat('j.n.Y H:i', $datetime );

            } else {
                $formatDatetime = DateTime::from($datetime);
            }

            return $formatDatetime;
        }

        return $datetime;
    }

    function __toString()
    {
        return $this->name;
    }


}