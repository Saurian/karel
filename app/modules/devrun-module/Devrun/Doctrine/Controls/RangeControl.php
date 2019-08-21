<?php


namespace Devrun\Doctrine\Controls;

use CmsModule\Forms\Controls\IRangeControl;
use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class RangeControl implements IComponentMapper
{

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * @var EntityManager
     */
    private $em;



    public function __construct(EntityFormMapper $mapper)
	{
		$this->mapper = $mapper;
        $this->em       = $this->mapper->getEntityManager();
        $this->accessor = $mapper->getAccessor();
	}



	/**
     * entity -> control
     *
	 * {@inheritdoc}
	 */
	public function load(ClassMetadata $meta, Component $component, $entity)
	{
        if (!$component instanceof IRangeControl || !$component instanceof BaseControl) {
            return FALSE;
        }

        if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {
            $newFromValue = $this->accessor->getValue($entity, $name);

            if ($meta->hasField($rangeName = $component->getOption(self::FIELD_RANGE_NAME, $component->getToValue()))) {
                $newToValue = $this->accessor->getValue($entity, $rangeName);



                $component->setValue([$newFromValue, $newToValue]);
            }

            return TRUE;
        }

        return FALSE;
	}



	/**
     * control -> entity
     *
	 * {@inheritdoc}
	 */
	public function save(ClassMetadata $meta, Component $component, $entity)
	{
//        return true;
        if (!$component instanceof IRangeControl || !$component instanceof BaseControl) {
            return FALSE;
        }

        if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {

            if ($meta->hasField($rangeName = $component->getOption(self::FIELD_RANGE_NAME, $component->getToName()))) {

                $fromValue = $component->getFromValue();
                $toValue = $component->getToValue();

                $this->accessor->setValue($entity, $name, $fromValue);
                $this->accessor->setValue($entity, $rangeName, $toValue);

                return TRUE;
            }
        }

		return FALSE;
	}

}
