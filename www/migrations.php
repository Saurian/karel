<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    migrations.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

$v = isset($_GET['v']) ? $_GET['v'] : null;

/*if ($v !== '123') {
    exit();
}*/

require __DIR__ . '/../migrations/run.php';
