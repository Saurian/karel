<?php

namespace Devrun\Doctrine\Controls;

use Devrun\Doctrine\DoctrineForms\EntityFormMapper;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Devrun\Doctrine\DoctrineForms\ToManyContainer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Replicator\Container;
use Nette;
use Nette\ComponentModel\Component;
use Tracy\Debugger;

class ToReplicator implements IComponentMapper
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
        if (!$component instanceof ToManyContainer) {
            return FALSE;
        }

        if (!$collection = $this->getCollection($meta, $entity, $name = $component->getName())) {
            return FALSE;
        }

//        dump($collection);
//        foreach ($component->getComponents() as $component) {
//            dump($component);
//        }


//        dump($component);
//        die();



        $em  = $this->mapper->getEntityManager();
        $UoW = $em->getUnitOfWork();

        $component->bindCollection($entity, $collection);



        if (!empty($collection)) {
//            dump($name);
//            dump($collection);


            /*
             * pokud nesouhlasí počet komponent s počtem kolekcí, komponenty smažeme.
             * jedná se o situaci při ajaxovém mazání z kontejneru, při běžném zobrazení je vše v pořádku, ale v ajaxu jsou komponenty pamatovány.
             */
            if (iterator_count($component->getComponents()) != $collection->count() ) {
                foreach ($component->getComponents() as $_component) {
                    $component->removeComponent($_component);
                }
            }


            foreach ($collection as $relation) {
//        dump($relation);


                if ($id = $UoW->getSingleIdentifierValue($relation)) {
                    $this->mapper->load($relation, $component[$id]);
                    continue;
                }

//                die(dump("Not support ManyToMany"));
//                dump($collection->indexOf($relation));
//                dump($relation);
//                dump($component);


                $this->mapper->load($relation, $component[ToManyContainer::NEW_PREFIX . $collection->indexOf($relation)]);
            }
            return TRUE;

        }

        return false;

    }


    /**
     * {@inheritdoc}
     */
    public function save(ClassMetadata $meta, Component $component, $entity)
    {
//        dump(__METHOD__);

        if (!$component instanceof Container) {
            return FALSE;
        }

        if (!$collection = $this->getCollection($meta, $entity, $component->getName())) {
            return FALSE;
        }

//        dump($collection);

        $em           = $this->mapper->getEntityManager();
        $class        = $meta->getAssociationTargetClass($component->getName());
        $relationMeta = $em->getClassMetadata($class);

//        dump($component);
//        dump($class);
//        dump($relationMeta);

//        die();
        /** @var Nette\Forms\Container $container */
        foreach ($component->getComponents(FALSE, 'Nette\Forms\Container') as $container) {

            $isNew = substr($container->getName(), 0, strlen(ToManyContainer::NEW_PREFIX)) === ToManyContainer::NEW_PREFIX;

//            dump($isNew);
//            die();

            $indexCollection = $this->getIndexCollectionOfId($relationMeta, $collection->toArray(), $container->getName());



            $name = $isNew ? substr($container->getName(), strlen(ToManyContainer::NEW_PREFIX)) : $indexCollection;

//            dump( $name);
//            dump($container->getName());
//            dump($indexCollection);
//            dump($name);

            if ($isNew) {
                $collection[$container->getName()] = $relation = new $relationMeta->name;
//                Debugger::barDump($collection);
//                Debugger::barDump($container->getName());

//                    Debugger::barDump($relationMeta);
//                    Debugger::barDump($relationMeta->getAssociationMappings());
                // inverse associations
                foreach ($relationMeta->getAssociationMappings() as $association => $assocMapping) {
                    $entity_class = $relationMeta->getAssociationTargetClass($association);

                    if ($entity instanceof $entity_class) {
                        if ($relation->$association instanceof Collection) {
//                            $relation->$association[$entity->id] = $entity;
//                            $relation->$association->add($entity);

                        } else {
                            $relation->$association = $entity;
                        }
                    }
                }

//                $em->persist($relation);

//                dump($relation);
//                die();

            } else {
                $relation = $collection->get($name);
            }

//
//            if (!$relation = $collection->get($name)) { // entity was added from the client
//
//                dump("ASD");
//
///*
//                if (!$component->isAllowedRemove()) {
//					continue;
//                }
//*/
//
//                $collection[$container->getName()] = $relation = new $relationMeta->name;
//
//                dump($container->getName());
//                dump($collection);
//                die();
//
//                // inverse associations
//                foreach ($relationMeta->getAssociationMappings() as $association => $assocMapping) {
//                    $entity_class = $relationMeta->getAssociationTargetClass($association);
//                    if ($entity instanceof $entity_class) {
//                        $relation->$association = $entity;
//                    }
//                }
//            }

//            dump($relation);
//            dump($container);
//            echo "<hr>";
            $this->mapper->save($relation, $container);
        }

        return TRUE;
    }


    /**
     * @param ClassMetadata $meta
     * @param object        $entity
     * @param string        $field
     *
     * @return Collection|boolean
     */
    private function getCollection(ClassMetadata $meta, $entity, $field)
    {
        if (!$meta->hasAssociation($field) || $meta->isSingleValuedAssociation($field)) {
            return FALSE;
        }

        $collection = $meta->getFieldValue($entity, $field);


        if ($collection === NULL) {

            $collection = new ArrayCollection();
            $meta->setFieldValue($entity, $field, $collection);
        }

        return $collection;
    }


    /**
     * @param ClassMetadata $meta
     * @param array         $collection
     *
     * @return array
     */
    private function sortCollection(ClassMetadata $meta, array $collection)
    {
        $id     = $meta->getSingleIdentifierFieldName();
        $result = array();
        array_walk($collection, function (&$value) use (&$result, $id) {
            $result[$value->$id] = $value;
        });
        return $result;
    }


    /**
     * @param ClassMetadata $meta
     * @param               $collectionArray
     * @param               $idValue
     *
     * @return array
     */
    private function getIndexCollectionOfId(ClassMetadata $meta, $collectionArray, $idValue)
    {
        $id     = $meta->getSingleIdentifierFieldName();
        $result = null;

        foreach ($collectionArray as $key => $obj) {
            if ($obj->$id == $idValue) {
                $result = $key;
                break;
            }
        }
        return $result;

    }


}
