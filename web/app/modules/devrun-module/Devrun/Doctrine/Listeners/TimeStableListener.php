<?php

namespace Devrun\Doctrine\Listeners;

use Devrun\Doctrine\Entities\DateTimeTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Events\Subscriber;
use Nette\Utils\DateTime;

/**
 * Class TimeStableListener
 *
 * @package CmsModule\Listeners
 */
class TimeStableListener implements Subscriber
{

    private $addScheduled = false;

    /**
     * Stores the current user into createdBy and updatedBy properties
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var DateTimeTrait $entity */
        $entity = $eventArgs->getEntity();
        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {

            if ($this->isTimeStable($classMetadata)) {
                $time = new DateTime();
                if (!$entity->getInserted()) {
                    $entity->setInserted($time);

                    if ($this->addScheduled) {
                        $uow->propertyChanged($entity, 'inserted', null, $time);
                        $uow->scheduleExtraUpdate($entity, [
                            'inserted' => [null, $time],
                        ]);
                    }
                }
                if (!$entity->getUpdated()) {
                    $entity->setUpdated($time);

                    if ($this->addScheduled) {
                        $uow->propertyChanged($entity, 'updated', null, $time);
                        $uow->scheduleExtraUpdate($entity, [
                            'updated' => [null, $time],
                        ]);
                    }
                }
            }

        }
    }


    /**
     * Stores the current user into updatedBy property
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var DateTimeTrait $entity */
        $entity = $eventArgs->getEntity();

        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {
            if ($this->isTimeStable($classMetadata)) {
                $entity->setUpdated($time = new DateTime());

                if ($this->addScheduled) {
                    $uow->propertyChanged($entity, 'updated', null, $time);
                    $uow->scheduleExtraUpdate($entity, [
                        'updated' => [null, $time],
                    ]);
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
    private function isTimeStable(ClassMetadata $class)
    {
        $className = version_compare(PHP_VERSION, '5.5.0')
            ? DateTimeTrait::class
            : DateTimeTrait::getDateTimeTraitName();

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
            Events::preUpdate,
        ];

    }
}
