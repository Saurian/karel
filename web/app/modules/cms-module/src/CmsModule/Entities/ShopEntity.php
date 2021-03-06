<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Class ShopEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\ShopRepository")
 * @ORM\Table(name="shop", indexes={
 *     @ORM\Index(name="name_idx", columns={"name"}),
 * })
 *
 * @package CmsModule\Entities
 */
class ShopEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name = 'Nová prodejna';


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $street;

    /**
     * @var string
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $strno;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $zip;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $city;


    /**
     * @var MetricEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricEntity", mappedBy="shop")
     */
    protected $metrics;


    /**
     * @var DeviceEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeviceEntity", mappedBy="shop", cascade={"persist"})
     */
    protected $devices;


    /**
     * @var UsersGroupEntity
     * @ORM\ManyToOne(targetEntity="UsersGroupEntity", inversedBy="shops")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $usersGroup;


    /**
     * @var DateTime
     * @ORM\Column(type="time")
     */
    protected $openTime;

    /**
     * @var DateTime
     * @ORM\Column(type="time")
     */
    protected $closeTime;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $openDayOfWeek = 1;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $closeDayOfWeek = 5;


    /**
     * ShopEntity constructor.
     */
    public function __construct()
    {
        $this->metrics = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ShopEntity
     */
    public function setName(string $name): ShopEntity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOpenTime()
    {
        return $this->openTime;
//        return $this->openTime ? $this->openTime->format('G') : null;
    }

    /**
     * @return int
     */
    public function getOpenHour()
    {
        return intval($this->openTime->format('G'));
    }

    /**
     * @param DateTime $openTime
     * @return ShopEntity
     */
    public function setOpenTime($openTime): ShopEntity
    {
        if (is_numeric($openTime)) {
            $openTime = DateTime::createFromFormat('H', $openTime);
        }

        $this->openTime = $openTime;
        return $this;
    }



    /**
     * @return \DateTime
     */
    public function getCloseTime()
    {
        return $this->closeTime;
//        return $this->closeTime ? $this->closeTime->format('G') : null;
    }

    /**
     * @return int
     */
    public function getCloseHour()
    {
        return intval($this->closeTime->format('G'));
    }

    /**
     * @param DateTime $closeTime
     * @return ShopEntity
     */
    public function setCloseTime($closeTime): ShopEntity
    {
        if (is_numeric($closeTime)) {
            $closeTime = DateTime::createFromFormat('H', $closeTime);
        }

        $this->closeTime = $closeTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getOpenDayOfWeek(): int
    {
        return $this->openDayOfWeek;
    }

    /**
     * @param int $openDayOfWeek
     * @return ShopEntity
     */
    public function setOpenDayOfWeek(int $openDayOfWeek): ShopEntity
    {
        $this->openDayOfWeek = $openDayOfWeek;
        return $this;
    }

    /**
     * @return int
     */
    public function getCloseDayOfWeek(): int
    {
        return $this->closeDayOfWeek;
    }

    /**
     * @param int $closeDayOfWeek
     * @return ShopEntity
     */
    public function setCloseDayOfWeek(int $closeDayOfWeek): ShopEntity
    {
        $this->closeDayOfWeek = $closeDayOfWeek;
        return $this;
    }

    /**
     * @param UsersGroupEntity $usersGroup
     * @return ShopEntity
     */
    public function setUsersGroup(UsersGroupEntity $usersGroup): ShopEntity
    {
        $this->usersGroup = $usersGroup;
        return $this;
    }





}