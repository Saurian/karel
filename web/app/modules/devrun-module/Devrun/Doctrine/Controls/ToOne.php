<?php


namespace Devrun\Doctrine\Controls;

use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette;
use Nette\ComponentModel\Component;



class ToOne implements IComponentMapper
{

	/**
	 * @var EntityFormMapper
	 */
	private $mapper;



	public function __construct(EntityFormMapper $mapper)
	{
		$this->mapper = $mapper;
	}



	/**
	 * {@inheritdoc}
	 */
	public function load(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof Nette\Forms\Container) {
			return FALSE;
		}

		if (!$relation = $this->getRelation($meta, $entity, $component->getName())) {
			return FALSE;
		}

		$this->mapper->load($relation, $component);
		return TRUE;
	}



	/**
	 * {@inheritdoc}
	 */
	public function save(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof Nette\Forms\Container) {
			return FALSE;
		}

		if (!$relation = $this->getRelation($meta, $entity, $component->getName())) {
			return FALSE;
		}

		$this->mapper->save($relation, $component);

		return TRUE;
	}



	/**
	 * @param ClassMetadata $meta
	 * @param object $entity
	 * @param string $field
	 * @return bool|object
	 */
	private function getRelation(ClassMetadata $meta, $entity, $field)
	{
		if (!$meta->hasAssociation($field) || !$meta->isSingleValuedAssociation($field)) {
			return FALSE;
		}

		// todo: allow access using property or method
		$relation = $meta->getFieldValue($entity, $field);
		if ($relation instanceof Collection) {
			return FALSE;
		}

		if ($relation === NULL) {
			$class = $meta->getAssociationTargetClass($field);
			$relationMeta = $this->mapper->getEntityManager()->getClassMetadata($class);

			$relation = $relationMeta->newInstance();
			$meta->setFieldValue($entity, $field, $relation);
		}

		return $relation;
	}

}
