<?php

namespace Devrun\Tests;

use Devrun\FileNotFoundException;
use Devrun\InvalidArgumentException;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\Environment;
use Nette\Http\FileUpload;
use Nette\Reflection\AnnotationsParser;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Image;
use PHPUnit\Framework\DOMTestTrait;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase {

    /** @var Container */
//    public $container;

    use DOMTestTrait;
    use FileTestTrait;

    public static $initDatabase = true;



    /**
     * implementace Nette inject metodiky pro pohodlnější testy
     *
     * @param string $name
     */
    private function _injectServices($name = 'inject')
    {
        $reflectClass = new \ReflectionClass(get_called_class());

        foreach ($reflectClass->getProperties() as $property) {
            $res = AnnotationsParser::getAll($property);
            if (isset($res[$name]) ? end($res[$name]) : NULL) {
                $this->injectService($property, $res['var']);
            }
        }
    }


    private function injectService(\ReflectionProperty $property, $resource)
    {
        if (isset($resource[0])) {

            try {
                $service      = $this->getContainer()->getByType($resource[0]);
                $_name        = $property->name;
                $this->$_name = $service;

            } catch (MissingServiceException $exc) {
                die(dump(sprintf('%s [%s] %s - full namespace ?', $exc->getMessage(), __METHOD__, $property->class)));
            }

        }
    }


    /**
     * @return Container
     */
    public static function getContainer()
    {
//        Environment::loadConfig();

//        return Environment::getContext();
        return $GLOBALS['container'];
    }


    /**
     * check uri
     *
     * @param string $uri uri
     *
     * @return mixed
     */
    protected function uriCheck($uri)
    {
        return (preg_replace('%^(.*)(\?.*)$%', '$1', $uri));
    }

    public static function setUpBeforeClass()
    {
        $reflectClass = new \ReflectionClass(get_called_class());
        $initDatabase = $reflectClass->getProperty("initDatabase")->getValue();

        if ($initDatabase) {
            \TestInit::initMigrations(self::getContainer());
        }

    }



    protected function setUp()
    {
        $annotations = $this->getAnnotations();

        /*
         * hack!, if some test methods is depending (previous method return) create new Container
         */
        if (isset($annotations['method']['return']) ) {
            global $container;
            global $_container;

            // $testMethod =$this->getName();
            // fwrite(STDOUT, $testMethod . "\n");

            $container = Environment::getContext();
            $container = new $_container;
        }

        $this->_injectServices();
    }

}

