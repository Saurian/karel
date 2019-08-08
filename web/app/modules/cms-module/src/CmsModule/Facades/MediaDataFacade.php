<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    MediaDataFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Facades;


use CmsModule\Entities\MediumDataEntity;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;
use Ublaboo\ImageStorage\ImageNameScript;
use Ublaboo\ImageStorage\ImageStorage;

class MediaDataFacade
{

    /** @var ImageStorage */
    private $imageStorage;

    private $dataPath;

    private $dataDir;

    /**
     * MediaDataFacade constructor.
     *
     * @param ImageStorage $imageStorage
     * @param string       $dataPath
     */
    public function __construct($dataPath, $dataDir, \Ublaboo\ImageStorage\ImageStorage $imageStorage)
    {
        $this->imageStorage = $imageStorage;
        $this->dataPath     = $dataPath;
        $this->dataDir      = $dataDir;
    }


    private function saveImageUpload(MediumDataEntity $mediumDataEntity, FileUpload $fileUpload)
    {
        if ($mediumDataEntity && $fileUpload->isImage()) {

            $namespace = $mediumDataEntity->getCampaign()->getId();
            $result    = $this->imageStorage->saveUpload($fileUpload, $namespace);

            // update medium?
            $mediumIdentifier = $mediumDataEntity->getIdentifier();
            if ($mediumIdentifier && ($result->identifier != $mediumIdentifier)) {
                $this->removeImageFile($mediumDataEntity);
            }

            $mediumDataEntity
                ->setType($fileUpload->contentType)
                ->setFileName($result->name)
                ->setIdentifier($result->identifier)
                ->setFilePath($result->data_dir . DIRECTORY_SEPARATOR . $result->identifier);
        }
    }


    public function saveFileUpload(MediumDataEntity $mediumDataEntity, FileUpload $fileUpload)
    {
        if ($mediumDataEntity && $fileUpload->isOk()) {

            if ($fileUpload->isImage()) {
                $this->saveImageUpload($mediumDataEntity, $fileUpload);

            } else {
                $fileName   = Strings::webalize($fileUpload->getName(), '_.');
                $targetDir  = $this->dataDir . DIRECTORY_SEPARATOR . $mediumDataEntity->getCampaign()->getId();
                $targetPath = $this->dataPath . DIRECTORY_SEPARATOR . $mediumDataEntity->getCampaign()->getId();

                if (!is_dir($targetPath)) {
                    @mkdir($targetPath, 0777, true);
                }

                /*
                 * remove old file
                 */
                $this->removeFile($mediumDataEntity);

                $fileNameCheck = $fileName;
                $postfixIndex  = 0;

                while (file_exists($target = $targetPath . DIRECTORY_SEPARATOR . $fileNameCheck)) {
                    $postfixIndex++;
                    $path_info     = pathinfo($fileName);
                    $fileNameCheck = $path_info['filename'] . "_$postfixIndex." . $path_info['extension'];
                }

                $mediumDataEntity
                    ->setType($fileUpload->contentType)
                    ->setFileName($fileNameCheck)
                    ->setFilePath($targetDir . DIRECTORY_SEPARATOR . $fileNameCheck);

                $fileUpload->move($target);
            }
        }

    }


    public function removeFileFromMedium(MediumDataEntity $mediumDataEntity)
    {
        if ($type = $mediumDataEntity->getType()) {

            if ($mediumDataEntity->isImage()) {
                $this->removeImageFile($mediumDataEntity);

            } else {
                $this->removeFile($mediumDataEntity);
            }

            $mediumDataEntity
                ->setType(null)
                ->setFileName(null)
                ->setIdentifier(null)
                ->setFilePath(null);

            return true;
        }

        return false;
    }


    private function removeImageFile(MediumDataEntity $mediumDataEntity)
    {
        if ($mediumIdentifier = $mediumDataEntity->getIdentifier()) {
            $this->imageStorage->delete($mediumDataEntity->getIdentifier());

            /*
             * delete empty dir
             */
            $script = ImageNameScript::fromName($mediumIdentifier);
            $dir    = implode(DIRECTORY_SEPARATOR, [$this->dataPath, $script->namespace, $script->prefix]);

            if (file_exists($dir)) {
                if ($isDirEmpty = !(new \FilesystemIterator($dir))->valid()) {
                    @rmdir($dir);
                }
            }
        }
    }

    private function removeFile(MediumDataEntity $mediumDataEntity)
    {
        $filename = implode(DIRECTORY_SEPARATOR, [$this->dataPath, $mediumDataEntity->getCampaign()->getId(), $mediumDataEntity->getFileName()]);

        /*
         * remove old file
         */
        if ($mediumDataEntity->getFileName() && file_exists($filename)) {
            @unlink($filename);
        }

    }


}