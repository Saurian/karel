<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class TargetGroupKeyValue
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\TargetGroupParamValueRepository")
 * @ORM\Table(name="target_group_value")
 *
 * @package CmsModule\Entities
 */
class TargetGroupParamValueEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;

    /**
     * value?
     *
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $name;


    /**
     * @var TargetGroupParamEntity
     * @ORM\ManyToOne(targetEntity="TargetGroupParamEntity", inversedBy="values")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $param;


    /**
     * @var TargetGroupEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="TargetGroupEntity", mappedBy="values")
     */
    protected $groups;





    /**
     * TargetGroupParamValueEntity constructor.
     * @param string $name
     * @param TargetGroupParamEntity $param
     */
    public function __construct(string $name = null, TargetGroupParamEntity $param = null)
    {
        $this->name = $name;
//        $this->param = $param;
        $this->groups = new ArrayCollection();
    }

    /**
     * @return TargetGroupParamEntity
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }





    public function addGroup()
    {

    }




}