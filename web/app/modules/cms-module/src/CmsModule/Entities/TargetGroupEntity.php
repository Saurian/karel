<?php


namespace CmsModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class TargetGroup
 * @ORM\Entity(repositoryClass="CmsModule\Repositories\TargetGroupRepository")
 * @ORM\Table(name="target_group")
 *
 * @package CmsModule\Entities
 * @method getName()
 */
class TargetGroupEntity
{

    use Identifier;
    use DateTimeTrait;
    use MagicAccessors;
    use BlameableTrait;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $name;

    /**
     * @var UsersGroupEntity
     * @ORM\ManyToOne(targetEntity="UsersGroupEntity", inversedBy="targets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $usersGroup;



    /**
     * @var TargetGroupParamValueEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="TargetGroupParamValueEntity", inversedBy="groups", cascade={"persist"})
     * @ORM\JoinTable(name="targets_param_values")
     */
    protected $values;


    /**
     * @var MetricEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="MetricEntity", mappedBy="targetGroup", cascade={"persist"})
     */
    protected $metrics;




    /**
     * TargetGroup constructor.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->values = new ArrayCollection();
    }


    /**
     * @param TargetGroupParamEntity $paramEntity
     * @return $this
     */
    public function addParam(TargetGroupParamEntity $paramEntity)
    {
        if (!$this->params->contains($paramEntity)) {
            $this->params->add($paramEntity);
        }
        return $this;
    }


    /**
     * @param TargetGroupParamEntity $paramEntity
     * @return $this
     */
    public function removeParam(TargetGroupParamEntity $paramEntity)
    {
        if ($this->params->contains($paramEntity)) {
            $this->params->remove($paramEntity);
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


    public function removeValue(TargetGroupParamValueEntity $valueEntity)
    {
        if ($this->values->contains($valueEntity)) {
            $this->values->removeElement($valueEntity);
        }
    }


    public function removeValueById($id)
    {
        foreach ($this->values as $key => $value) {
            if ($id == $value->getId()) {
                $this->values->remove($key);
            }
        }
    }



}