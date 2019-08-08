<?php


namespace Devrun\Doctrine\Controls;

use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Devrun\Doctrine\InvalidStateException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\PersistentCollection;
use Nette;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\CheckboxList;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tracy\Debugger;


/**
 */
class TextControl implements IComponentMapper
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
        $this->mapper   = $mapper;
        $this->em       = $this->mapper->getEntityManager();
        $this->accessor = $mapper->getAccessor();
    }

    private function changeToDisableComponent(Component $component)
    {
        $component_name = $component->getName();
        $component_value = $component->getValue();
    }


    /**
     * {@inheritdoc}
     */
    public function load(ClassMetadata $meta, Component $component, $entity)
    {
        if (!$component instanceof BaseControl) {
            return FALSE;
        }

        if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {

            // bug repair, component can have boolean type (select items [0,1]), false value transform to 0
            $newValue = $this->accessor->getValue($entity, $name);
            if ($component->getOption(self::FIELD_TYPE) && $newValue === FALSE) {
                $newValue = 0;
            }
            if ($component->getOption(self::FIELD_IGNORE, false) == false) {
                $component->setValue($newValue);
            }
            return TRUE;
        }


//        if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {
//
////            $q = new Nette\Forms\Controls\TextArea();
//
//            if ($component instanceof Nette\Forms\Controls\TextBase  ) {
//
//                dump($component->name);
//
////                $newClass = get_class($component);
//
//
//                /** @var BaseControl $disabled */
////                $disabled = new $newClass;
//
//    //            $disabled->getControlPrototype()->id =  'asd';
////                $disabled->getControlPrototype()->style('color', 'red');
//    //            $disabled->setValue($this->accessor->getValue($entity, $name));
//
////                $disName = "{$component->getName()}_dis";
//
////                dump($disName);
//
////                dump($disabled);
//
////                $parent = $component->getParent();
////                $parent->addComponent($disabled, $disName);
//
//    //            dump($parent);
//    ////
//    //            die();
//
//
//    //            dump($component->getControlPrototype()->setName('efef'));
//
//
//    //            $component->getParent()->addComponent($disabled);
//
////                dump($component->getName());
//
//                $component->setValue($this->accessor->getValue($entity, $name));
//
////                    ->getControlPrototype()->setName('input')->data('data-nette-rules', [])
////                    ->setValue($this->accessor->getValue($entity, $name))
////                    ->type('hidden');
//
//            }
//
//            return TRUE;
//        }

        if (!$meta->hasAssociation($name)) {
            return FALSE;
        }

        /** @var SelectBox|RadioList|MultiSelectBox|CheckboxList $component */
        if (($component instanceof SelectBox ||
             $component instanceof RadioList ||
             $component instanceof MultiSelectBox ||
             $component instanceof CheckboxList) && !count($component->getItems())
        ) {
            // items load
            if (!$nameKey = $component->getOption(self::ITEMS_TITLE, FALSE)) {
                $path = $component->lookupPath('Nette\Application\UI\Form');
                throw new InvalidStateException(
                    'Either specify items for ' . $path . ' yourself, or set the option Kdyby\DoctrineForms\IComponentMapper::ITEMS_TITLE ' .
                    'to choose field that will be used as title'
                );
            }

            $criteria = $component->getOption(self::ITEMS_FILTER, array());
            $orderBy  = $component->getOption(self::ITEMS_ORDER, array());

            $related = $this->relatedMetadata($entity, $name);
            $items   = $this->findPairs($related, $criteria, $orderBy, $nameKey);
            $component->setItems($items);
        }

        // values load
        if (!count($component->getValue())) {
            $relationMapping = $meta->getAssociationMapping($name);
            if ($relationMapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {

                /** @var $component MultiSelectBox || CheckboxList */
                if ($component instanceof Nette\Forms\Controls\MultiSelectBox ||
                    $component instanceof Nette\Forms\Controls\CheckboxList
                ) {

                    if ($component->getOption(self::FIELD_IGNORE, false) == false) {
                        if ($relation = $this->accessor->getValue($entity, $name)) {

//                        dump($relation);


                            $UoW = $this->em->getUnitOfWork();
                            $values = array();
                            foreach ($relation as $value) {
                                $id       = $UoW->getSingleIdentifierValue($value);
                                $values[] = $id;
                            }

//                        Debugger::barDump($values);
//                        dump($values);
//                        die();

                            $component->setValue($values);
                            return TRUE;
                        }
                    }
                }

                return FALSE;

            } elseif ($relationMapping['type'] == ClassMetadataInfo::ONE_TO_MANY) {
                if ($component instanceof Nette\Forms\Controls\MultiSelectBox ||
                    $component instanceof Nette\Forms\Controls\CheckboxList
                ) {
                    // items load
                    if (!$nameKey = $component->getOption(self::ITEMS_TITLE, FALSE)) {
                        $path = $component->lookupPath('Nette\Application\UI\Form');
                        throw new InvalidStateException(
                            'Either specify items for ' . $path . ' yourself, or set the option Kdyby\DoctrineForms\IComponentMapper::ITEMS_TITLE ' .
                            'to choose field that will be used as title'
                        );
                    }

                    $criteria = $component->getOption(self::ITEMS_FILTER, array());
                    $orderBy  = $component->getOption(self::ITEMS_ORDER, array());

                };


                /** @var PersistentCollection $relation */
                $relation = $this->accessor->getValue($entity, $name);

                $UoW = $this->em->getUnitOfWork();
                $values = array();
                foreach ($relation as $value) {
                    $id       = $UoW->getSingleIdentifierValue($value);
                    $values[] = $id;
                }

                $component->setValue($values);
                return TRUE;

            } else {

                /** @var ArrayCollection $relation */
                if ($relation = $this->accessor->getValue($entity, $name)) {
                    $UoW = $this->em->getUnitOfWork();
                    $component->setValue($UoW->getSingleIdentifierValue($relation));
                }
            }
        }

        return TRUE;
    }


    /**
     * @param string|object $entity
     * @param string        $relationName
     *
     * @return ClassMetadata|\Kdyby\Doctrine\Mapping\ClassMetadata
     */
    private function relatedMetadata($entity, $relationName)
    {
        $meta        = $this->em->getClassMetadata(is_object($entity) ? get_class($entity) : $entity);
        $targetClass = $meta->getAssociationTargetClass($relationName);
        return $this->em->getClassMetadata($targetClass);
    }


    /**
     * @param ClassMetadata $meta
     * @param array         $criteria
     * @param array         $orderBy
     * @param string        $nameKey
     *
     * @return array
     */
    private function findPairs(ClassMetadata $meta, $criteria, $orderBy, $nameKey)
    {
        $repository = $this->em->getRepository($meta->getName());

        if ($repository instanceof \Kdyby\Doctrine\EntityDao) {
            return $repository->findPairs($criteria, $nameKey, $orderBy);
        }

        $items = array();
        $idKey = $meta->getSingleIdentifierFieldName();
        foreach ($repository->findBy($criteria, $orderBy) as $entity) {
            $items[$this->accessor->getValue($entity, $idKey)] = $this->accessor->getValue($entity, $nameKey);
        }

        return $items;
    }


    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, $entity)
    {
        if (!$component instanceof BaseControl) {
            return FALSE;
        }

        if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {

            $value = $component->getValue() instanceof Nette\Http\FileUpload
                ? $component->getValue()->name
                : $component->getValue();

            $this->accessor->setValue($entity, $name, $value);
            return TRUE;
        }

        if (!$meta->hasAssociation($name)) {
            return FALSE;
        }

        if (!$identifier = $component->getValue()) {
            /*
             * if entity value is set and new value is not set, entity value will have removed
             */
//            if ($this->accessor->getValue($entity, $name))
//                $this->accessor->setValue($entity, $name, null);

            return FALSE;
        }


        $repository = $this->em->getRepository($this->relatedMetadata($entity, $name)->getName());

        if (is_array($identifier)) {

            /** @var $targetEntity PersistentCollection */
            if ((!($targetEntity = $this->accessor->getValue($entity, $name)) instanceof PersistentCollection) &&
                (!$targetEntity instanceof ArrayCollection)) {
                throw new InvalidStateException('Set getter "' . $name . '" in ' . get_class($entity) . " to ArrayCollection");
            }

            $relations = $repository->findAssoc(array());
            foreach ($relations as $id => $relation) {
                if ($targetEntity->contains($relations[$id]) && !in_array($id, $identifier)) {
//                    $targetEntity->removeElement($relation);

                } elseif (!$targetEntity->contains($relations[$id]) && in_array($id, $identifier)) {
                    $targetEntity->add($relation);
                }
            }

            $meta->setFieldValue($entity, $name, $targetEntity);

        } else {
            if ($relation = $repository->find($identifier)) {
                $meta->setFieldValue($entity, $name, $relation);
            }
        }


        return TRUE;
    }

}
