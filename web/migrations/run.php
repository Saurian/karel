<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    run.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

use Nextras\Migrations\Bridges;
use Nextras\Migrations\Controllers;
use Nextras\Migrations\Drivers;
use Nextras\Migrations\Extensions;

/** @var \Nette\DI\Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

/** @var \Kdyby\Doctrine\EntityManager $em */
$em = $container->getByType('Kdyby\Doctrine\EntityManager');

$conn = $em->getConnection();

$dbal = new Bridges\DoctrineDbal\DoctrineAdapter($conn);
$driver = new Drivers\MySqlDriver($dbal);
$controller = php_sapi_name() === 'cli'
    ? new Controllers\ConsoleController($driver)
    : new Controllers\HttpController($driver);

$baseDir = __DIR__;
$controller->addGroup('structures', "$baseDir/structures");
$controller->addGroup('basic-data', "$baseDir/basic-data", array('structures'));
$controller->addGroup('dummy-data', "$baseDir/dummy-data", array('basic-data'));
$controller->addGroup('production', "$baseDir/production", array('basic-data'));
$controller->addExtension('sql', new Extensions\SqlHandler($driver));
$controller->addExtension('php', new Extensions\PhpHandler(['container' => $container]));
$controller->run();
