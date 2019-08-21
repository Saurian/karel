<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    FileTestTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Tests;


use Devrun\FileNotFoundException;
use Devrun\InvalidArgumentException;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Image;

trait FileTestTrait
{

    private function getFileTypeMask($type)
    {
        $types = [
            'image/png'  => '*.png',
            'image/jpeg' => '*.jpg',
        ];

        if (!isset($types[$type])) {
            throw new InvalidArgumentException("define mask for type `$type`  [". __FUNCTION__ .'], ' . implode(', ', array_keys($types)));
        }

        return $types[$type];
    }


    public function findTestFile($type = 'image/jpeg', string $findFileName = null)
    {
        $dir   = __DIR__ . '/files';
        $mask  = $this->getFileTypeMask($type);
        $files = Finder::findFiles($mask)->from($dir);
        if ($files->count() == 0) {
            throw new FileNotFoundException("`$mask` not found to createMockFile");
        }

        $index  = 0;
        $source = '';
        $count  = rand(0, $files->count() - 1);
        foreach ($files as $file) {
            if ($findFileName && (string) basename($file) == $findFileName ) {
                $source = (string) $file;
                break;

            } else {
                if ($count == $index) {
                    $source = (string) $file;
                    break;
                }
            }
            $index++;
        }

        return $source;
    }


    public function createMockFile($type = 'image/jpeg', string $findFileName = null)
    {
        if (!$source = $this->findTestFile($type, $findFileName)) {
            throw new FileNotFoundException("nenalezen žádný testovací soubor pro masku $mask");
        }

        $put = TEMP_DIR . DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR . basename($source);

        FileSystem::copy($source, $put, true);
        return $put;
    }


    /**
     * @param string $type
     *
     * @return FileUpload
     */
    public function createUploadFile($type = 'image/jpeg')
    {
        $source = $this->createMockFile(($type));

        $value = [
            'name' => basename($source),
            'type' => $type,
            'size' => filesize($source),
            'tmp_name' => $source,
            'error' => 0
        ];

        return new FileUpload($value);
    }


    /**
     * @param string $type
     *
     * @return Image
     */
    public function createTestImage($type = 'image/jpeg')
    {
        $source = $this->findTestFile($type);
        return Image::fromFile($source);
    }




}