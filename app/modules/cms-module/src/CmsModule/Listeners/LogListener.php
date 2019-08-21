<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    LogListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Listeners;

use CmsModule\Entities\CampaignEntity;
use CmsModule\Entities\DeviceEntity;
use CmsModule\Entities\DeviceGroupEntity;
use CmsModule\Entities\LogEntity;
use CmsModule\Entities\UserEntity;
use CmsModule\Forms\BaseForm;
use CmsModule\Repositories\LogRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Kdyby\Monolog\Logger;

class LogListener implements Subscriber
{

    /** @var EntityManager */
    private $entityManager;

    /** @var LogRepository */
    private $logRepository;

    /** @var Logger */
    private $logger;


    /**
     * LogListener constructor.
     *
     * @param EntityManager $entityManager
     * @param LogRepository $logRepository
     * @param Logger        $logger
     */
    public function __construct(EntityManager $entityManager, LogRepository $logRepository, Logger $logger)
    {
        $this->entityManager = $entityManager;
        $this->logRepository = $logRepository;
        $this->logger        = $logger;
    }


    public function onUserActivate(UserEntity $userEntity)
    {
        $message = $userEntity->isActive()
            ? 'activated'
            : 'deactivated';

        $this->logger->info("{$userEntity->getRole()} {$userEntity->getUsername()} has been $message", ['type' => LogEntity::ACTION_ACCOUNT, 'target' => $userEntity, 'action' => 'switch activation handle']);
    }


    public function onDeviceActivate(DeviceEntity $deviceEntity)
    {
        $message = $deviceEntity->isActive()
            ? 'activated'
            : 'deactivated';

        $this->logger->info("device `{$deviceEntity->getName()}` has been $message", ['type' => LogEntity::ACTION_DEVICE, 'target' => $deviceEntity, 'action' => 'switch activation handle']);
    }


    public function onDeviceGroupActivate(DeviceGroupEntity $entity)
    {
        $message = $entity->isActive()
            ? 'activated'
            : 'deactivated';

        $this->logger->info("deviceGroup `{$entity->getName()}` has been $message", ['type' => LogEntity::ACTION_DEVICE_GROUP, 'target' => $entity, 'action' => 'switch activation handle']);
    }


    public function onCampaignActivate(CampaignEntity $campaignEntity)
    {
        $message = $campaignEntity->isActive()
            ? 'activated'
            : 'deactivated';

        $this->logger->info("campaign `{$campaignEntity->getName()}` has been $message", ['type' => LogEntity::ACTION_CAMPAIGN, 'target' => $campaignEntity, 'action' => 'switch activation handle']);
    }




    public function onDeviceGroupFormSuccess(BaseForm $form)
    {
        $this->persistFormSuccess('device group', $form, __FUNCTION__);
    }


    public function onDeviceFormSuccess(BaseForm $form)
    {
        $this->persistFormSuccess('device', $form, __FUNCTION__);
    }


    public function onCampaignFormSuccess(BaseForm $form)
    {
        $this->persistFormSuccess('campaign', $form, __FUNCTION__);
    }


    public function onTemplateFormSuccess(BaseForm $form)
    {
        $this->persistFormSuccess('template', $form, __FUNCTION__);
    }


    public function onUserFormSuccess(BaseForm $form)
    {
        $entity = $form->getEntity();
        $this->persistFormSuccess("user {$entity->role} ", $form, __FUNCTION__);
    }


    public function onRegistrationFormSuccess(BaseForm $form)
    {
        $entity = $form->getEntity();
        $this->persistFormSuccess("user {$entity->role} ", $form, __FUNCTION__);
    }


    public function onForgottenPasswordFormSuccess(BaseForm $form)
    {
        $entity = $form->getEntity();
        $this->persistFormSuccess("user {$entity->role} ", $form, __FUNCTION__);
    }


    public function onChangePasswordForm(BaseForm $form)
    {
        $entity = $form->getEntity();
        $this->persistFormSuccess("user {$entity->role} ", $form, __FUNCTION__);
    }


    private function persistFormSuccess($name, BaseForm $form, $action)
    {

        $entity      = $form->getEntity();
        $persistType = $entity->getId() ? "updated" : "inserted";
        $formName    = $form->getFormName();

        $this->logger->info("$name [$formName] `{$entity->getName()}` has been $persistType", ['type' => LogEntity::ACTION_FORM, 'target' => $entity, 'action' => $action, 'name' => get_class($form)]);
    }


    /**
     * update log target key if insertion entity
     *
     * @param LifecycleEventArgs $lifecycleEventArgs
     */
    public function postPersist(LifecycleEventArgs $lifecycleEventArgs)
    {
        $entity = $lifecycleEventArgs->getEntity();
        $em     = $lifecycleEventArgs->getEntityManager();
        $uow    = $lifecycleEventArgs->getEntityManager()->getUnitOfWork();

        $className  = get_class($entity);
        $insertions = $uow->getScheduledEntityInsertions();

        foreach ($insertions as $id => $insertion) {

            if ($insertion instanceof LogEntity) {
                if ($insertion->getTargetKey() == null && $insertion->getTarget() == $className) {
                    $insertion->setTargetKey($entity->id);
                    $metaData = $em->getClassMetaData(get_class($insertion));
                    $uow->recomputeSingleEntityChangeSet($metaData, $insertion);
                }
            }

        }
    }


    function getSubscribedEvents()
    {
        return [
            'CmsModule\Facades\UserFacade::onActivate'   => [array('onUserActivate', 20)],
            'CmsModule\Facades\DeviceFacade::onActivate' => [array('onDeviceActivate', 20)],
            'CmsModule\Facades\DeviceFacade::onGroupActivate' => [array('onDeviceGroupActivate', 20)],
            'CmsModule\Facades\CampaignFacade::onActivate' => [array('onCampaignActivate', 20)],

            'CmsModule\Forms\DeviceGroupForm::onSuccess' => [array('onDeviceGroupFormSuccess', 20)],
            'CmsModule\Forms\CampaignForm::onSuccess'      => [array('onCampaignFormSuccess', 20)],
            'CmsModule\Forms\DeviceForm::onSuccess'      => [array('onDeviceFormSuccess', 20)],
            'CmsModule\Forms\UserForm::onSuccess'      => [array('onUserFormSuccess', 20)],
            'CmsModule\Forms\RegistrationForm::onSuccess'      => [array('onRegistrationFormSuccess', 20)],
            'CmsModule\Forms\ForgottenPasswordForm::onSuccess'      => [array('onForgottenPasswordFormSuccess', 20)],
            'CmsModule\Forms\ChangePasswordForm::onSuccess'      => [array('onChangePasswordForm', 20)],
            'CmsModule\Forms\TemplateForm::onSuccess'      => [array('onTemplateFormSuccess', 20)],

            Events::postPersist,
        ];
    }
}