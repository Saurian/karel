<?php

namespace Devrun\Doctrine\Listeners;

use CmsModule\Entities\UserEntity;
use CmsModule\Repositories\UserRepository;
use Devrun\Doctrine\Entities\BlameableTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Events\Subscriber;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * Class BlameableListener
 *
 * @package CmsModule\Listeners
 */
class BlameableListener implements Subscriber
{

    /** @var Container */
    protected $container;

    /** @var UserEntity */
    private $currentUser;

    /** @var UserRepository */
    private $userRepository;

    /** @var User */
    private $user;

    private $addScheduled = false;

    public function __construct(UserRepository $userRepository, User $user)
    {
        $this->user           = $user;
        $this->userRepository = $userRepository;
    }


    /**
     * Stores the current user into createdBy and updatedBy properties
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var BlameableTrait $entity */
        $entity = $eventArgs->getEntity();
        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {

            if ($this->isBlameable($classMetadata)) {
                if (!$entity->getCreatedBy()) {
                    if ($user = $this->getCurrentUser()) {
                        $entity->setCreatedBy($user);

                        if ($this->addScheduled) {
                            $uow->propertyChanged($entity, 'createdBy', null, $user);
                            $uow->scheduleExtraUpdate($entity, [
                                'createdBy' => [null, $user],
                            ]);
                        }
                    }
                }
                if (!$entity->getUpdatedBy()) {
                    if ($user = $this->getCurrentUser()) {
                        $entity->setUpdatedBy($user);
                        if ($this->addScheduled) {
                            $uow->propertyChanged($entity, 'updatedBy', null, $user);
                            $uow->scheduleExtraUpdate($entity, [
                                'updatedBy' => [null, $user],
                            ]);
                        }
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

        /** @var BlameableTrait $entity */
        $entity = $eventArgs->getEntity();

        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {

            if ($this->isBlameable($classMetadata)) {
                if ($user = $this->getCurrentUser()) {
                    if ($oldValue = $entity->getUpdatedBy()) {

                        if ($user->getId() != $oldValue->getId()) {
                            $entity->setUpdatedBy($user);

                            if ($this->addScheduled) {
                                $uow->propertyChanged($entity, 'updatedBy', $oldValue, $user);
                                $uow->scheduleExtraUpdate($entity, [
                                    'updatedBy' => [$oldValue, $user],
                                ]);
                            }
                        }

                    } else {
                        $entity->setUpdatedBy($user);

                        if ($this->addScheduled) {
                            $uow->propertyChanged($entity, 'updatedBy', $oldValue, $user);
                            $uow->scheduleExtraUpdate($entity, [
                                'updatedBy' => [$oldValue, $user],
                            ]);
                        }
                    }
                }
            }

        }
    }

    /**
     * Stores the current user into deletedBy property
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var BlameableTrait $entity */
        $entity = $eventArgs->getEntity();

        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {

            if ($this->isBlameable($classMetadata)) {
                if ($user = $this->getCurrentUser()) {
                    $oldValue = $entity->getDeletedBy();
                    $entity->setDeletedBy($user);

                    if ($this->addScheduled) {
                        $uow->propertyChanged($entity, 'deletedBy', $oldValue, $user);
                        $uow->scheduleExtraUpdate($entity, [
                            'deletedBy' => [$oldValue, $user],
                        ]);
                    }
                }
            }
        }
    }


    /**
     * @return UserEntity
     */
    public function getCurrentUser()
    {
        if (NULL === $this->currentUser) {
            if ($this->user->isLoggedIn()) {
                if ($userEntity = $this->getUserRepository()->getEntityManager()->getRepository(UserEntity::getClassName())->find($this->user->getIdentity()->getId())) {
                    $this->currentUser = $userEntity;
                }
            }
        }

        return $this->currentUser;
    }


    /**
     * @return UserRepository
     */
    private function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * Return is blameable entity
     *
     * @param ClassMetadata $class
     *
     * @return bool is blameable entity
     */
    private function isBlameable(ClassMetadata $class)
    {
        $className = version_compare(PHP_VERSION, '5.5.0')
            ? BlameableTrait::class
            : BlameableTrait::getBlameableTraitName();

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
            Events::preRemove,  //temporary disabled
        ];

    }
}
