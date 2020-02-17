<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class StrategyEntity
 * @ORM\Entity
 * @ORM\Table(name="strategy")
 *
 * @package CmsModule\Entities
 */
class StrategyEntity
{

    private $default_tag = null;

    private static $names = [
        'TippingPoint' => 'Tipping point',
        'GrandSlam' => 'Grand slam',
        'Kometa' => 'Kometa',
        'SoapOpera' => 'Soap opera',
    ];


    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=32)
     * @var string
     */
    private $id;


    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $name;


    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $params = [];


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $comment;


    /**
     * @var CampaignEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="CampaignEntity", mappedBy="strategy")
     */
    protected $campaigns;


    /**
     * StrategyEntity constructor.
     * @param string $name
     * @param array $params
     */
    public function __construct(string $name, array $params = [])
    {
        $this->name = $name;
        $this->params = $params;
        $this->campaigns = new ArrayCollection();
    }


    /**
     * @return array
     */
    public static function getNames()
    {
        return self::$names;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return StrategyEntity
     */
    public function setParams(array $params): StrategyEntity
    {
        $this->params = $params;
        return $this;
    }


    /**
     * @return array
     */
    public function getTiming()
    {
        return isset($this->params['timing']) ? $this->params['timing'] : [];
    }

}