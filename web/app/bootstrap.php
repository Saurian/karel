<?php

// Load Nette Framework or autoloader generated by Composer
use Devrun\Utils\Debugger;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Config\Configurator;

//$configurator->addParameters(array('wwwDir' => $wwwDir = dirname(__DIR__) . '/www' ));

$myIP = '37.221.241.103';
//$myIP = '89.221.216.136';
$configurator->setDebugMode([$myIP, $remoteIP = '172.18.0.1']);
$configurator->enableDebugger(__DIR__ . '/../log', 'pavel.paulik@seznam.cz');
//$configurator->setDebugMode(false);

error_reporting(~E_USER_DEPRECATED); // note ~ before E_USER_DEPRECATED
umask(0000);

// Specify folder for cache
$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$robotLoader = $configurator->createRobotLoader();
$robotLoader->ignoreDirs = $robotLoader->ignoreDirs . ', Tests';
$robotLoader
	->addDirectory(__DIR__)
	->register();

if (Devrun\Utils\Debugger::getIPAddress() == $myIP) {
    $configurator->setDebugMode(true);
}
//$configurator->setDebugMode(false);



//Devrun\Utils\Debugger::isDocker()
$environment = (Nette\Configurator::detectDebugMode(['127.0.0.1', $remoteIP]) or (PHP_SAPI == 'cli' && Nette\Utils\Strings::startsWith(getHostByName(getHostName()), "127.0.")))
    ? 'development'
    : 'production';

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . "/config/config.$environment.neon");
$container = $configurator->createContainer();

Devrun\Doctrine\DoctrineForms\ToManyContainer::register();

CmsModule\Forms\Controls\BootstrapDatePicker::register('j. n. Y', 'cs', 'addBootstrapDatePicker');
CmsModule\Forms\Controls\BootstrapDateRangePicker::register('j. n. Y H:i', 'cs', 'addBootstrapDateRangePicker');

return $container;
