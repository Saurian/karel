<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediaDataListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Listeners;

use CmsModule\Entities\MediumDataEntity;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Ublaboo\ImageStorage\ImageStorage;

class MediaDataListener implements Subscriber
{

    /** @var string wwwDir */
    private $dataPatch;

    /** @var ImageStorage */
    private $imageStorage;


    /**
     * MediaDataListener constructor.
     *
     * @param              $dataPatch
     * @param ImageStorage $imageStorage
     */
    public function __construct($dataPatch, ImageStorage $imageStorage)
    {
        $this->dataPatch    = $dataPatch;
        $this->imageStorage = $imageStorage;
    }


    /**
     * after remove entity, remove medium too
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        /** @var MediumDataEntity $entity */
        $entity = $eventArgs->getEntity();

        if ($entity instanceof MediumDataEntity) {
            $fileName = $this->dataPatch . DIRECTORY_SEPARATOR . $entity->getFilePath();

            if ($entity->isImage()) {
                $this->imageStorage->delete($entity->getIdentifier());

            } else {
                if ($entity->getFilePath() && file_exists($fileName)) {
                    @unlink($fileName);
                }
            }

            $path_info = pathinfo($fileName);
            if ($isDirEmpty = !(new \FilesystemIterator($dir = $path_info['dirname']))->valid()) {
                @rmdir($dir);
            }

        }
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
        ];
    }
}