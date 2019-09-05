<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class TargetGroupKey
 * @ORM\Entity
 * @ORM\Table(name="target_group_param")
 *
 * @package CmsModule\Entities
 * @method getName()
 */
class TargetGroupParamEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;


    /**
     * @var string
     * @example pohlavi|age
     * @ORM\Column(type="string", length=128)
     */
    protected $name;



    /**
     * @var TargetGroupParamValueEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TargetGroupParamValueEntity", mappedBy="param", cascade={"persist"})
     */
    protected $values;







    /**
     * @var UsersGroupEntity
     * @ORM\ManyToOne(targetEntity="UsersGroupEntity", inversedBy="targetParams")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $usersGroup;


    /**
     * TargetGroupParamEntity constructor.
     * @param string|null $name
     * @param TargetGroupEntity|null $targetGroupEntity
     */
    public function __construct(string $name = null, TargetGroupEntity $targetGroupEntity = null)
    {
        $this->name = $name;
        $this->values = new ArrayCollection();
    }


    /**
     * @param TargetGroupParamValueEntity $paramEntity
     * @return $this
     */
    public function addValue(TargetGroupParamValueEntity $valueEntity)
    {
        if (!$this->values->contains($valueEntity)) {
            $this->values->add($valueEntity);
        }
        return $this;
    }


    /**
     * @param TargetGroupParamValueEntity $paramEntity
     * @return $this
     */
    public function removeParam(TargetGroupParamValueEntity $valueEntity)
    {
        if ($this->values->contains($valueEntity)) {
            $this->values->remove($valueEntity);
        }
        return $this;
    }

    /**
     * @return TargetGroupParamValueEntity[]|ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }




}