<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class ShopEntity
 * @ORM\Entity
 * @ORM\Table(name="shop")
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
    protected $name;


    /**
     * @var MetricEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricEntity", mappedBy="shop")
     */
    protected $metrics;


    /**
     * @var string
     * @ORM\Column(type="time")
     */
    protected $openTime;

    /**
     * @var string
     * @ORM\Column(type="time")
     */
    protected $closeTime;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $openDayOfWeek;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $closeDayOfWeek;


    /**
     * ShopEntity constructor.
     */
    public function __construct()
    {
        $this->targets = new ArrayCollection();
        $this->metrics = new ArrayCollection();

    }


}