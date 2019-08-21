<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    PositionListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Listeners;

use Devrun\Doctrine\Entities\PositionTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Events\Subscriber;

class PositionListener implements Subscriber
{


    /**
     * Stores the current user into createdBy and updatedBy properties
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();


        /** @var PositionTrait $entity */
        $entity = $eventArgs->getEntity();
        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {

            if ($this->isPositionable($classMetadata)) {

                $info = $em->getRepository($classMetadata->getName())->createQueryBuilder('e')
                    ->select('COUNT(e.id) as num, MAX(e.position) as max_positions')
                    ->getQuery()
                    ->getOneOrNullResult();

                $maxPositions = $info['max_positions'];

                if (!$entity->position) {
                    $entity->position = $maxPositions + 1;
                }

            }
        }
    }


    /**
     * Return is timeStable entity
     *
     * @param ClassMetadata $class
     *
     * @return bool is timeStable entity
     */
    private function isPositionable(ClassMetadata $class)
    {
        $className = version_compare(PHP_VERSION, '5.5.0')
            ? PositionTrait::class
            : PositionTrait::getPositionTraitName();

        return in_array($className, $class->getReflectionClass()->getTraitNames());
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
//            Events::preUpdate,
        ];

    }

}