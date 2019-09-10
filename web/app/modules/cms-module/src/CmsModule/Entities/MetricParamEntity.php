<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class MetricParamEntity
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\MetricParamRepository")
 * @ORM\Table(name="metric_param")
 *
 * @package CmsModule\Entities
 * @method getName(): string
 */
class MetricParamEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;

    /**
     * @example návštěvnost|zaplnění prodejny
     *
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $name;

    /**
     * @var MetricEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricEntity", mappedBy="metricParam")
     */
    protected $metrics;



    /**
     * @var UsersGroupEntity
     * @ORM\ManyToOne(targetEntity="UsersGroupEntity", inversedBy="metricParams")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $usersGroup;

    /**
     * MetricParamEntity constructor.
     */
    public function __construct(string $name = null)
    {
        $this->name = $name;
        $this->metrics = new ArrayCollection();
    }


}