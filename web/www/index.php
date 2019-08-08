<?php

/* TEST DEPLOY DEV */

declare(strict_types=1);

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//declare(strict_types=1);
// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';

$container = require __DIR__ . '/../app/bootstrap.php';
$container->getByType(Nette\Application\Application::class)->run();
