<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    ExecController.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Migrations\Controllers;

use Nextras\Migrations\Controllers\BaseController;
use Nextras\Migrations\Engine;
use Nextras\Migrations\Printers;

class ExecController extends BaseController
{


    public function run($action = null, $groups = [], $mode = Engine\Runner::MODE_CONTINUE)
    {
        $this->processArguments($action, $groups, $mode);
        $this->printHeader();
        $this->registerGroups();
        $this->runner->run($this->mode);
    }


    private function printHeader()
    {
        if ($this->mode === Engine\Runner::MODE_INIT) {
            printf("-- Migrations init\n");
        } else {
            printf("Migrations\n");
            printf("------------------------------------------------------------\n");
        }
    }


    private function processArguments($action, array $groups = [], $mode)
    {
        $help = false;
        $useGroups = $error = FALSE;

        $this->mode = $mode;
        if (!$action && !$groups) {
            $help = true;
        }

        foreach ($groups as $group) {
            if (isset($this->groups[$group])) {
                $this->groups[$group]->enabled = TRUE;
                $useGroups = TRUE;
            } else {
                fprintf(STDERR, "Error: Unknown group '%s'\n", $group);
                $error = TRUE;
            }
        }

        if (!$useGroups && !$help) {
            fprintf(STDERR, "Error: At least one group must be enabled.\n");
            $error = TRUE;
        }

        if ($error) {
            printf("\n");
        }

        if ($help || $error) {
            printf("Usage: %s group1 [, group2, ...] [--reset] [--help]\n", basename($_SERVER['argv'][0]));
            printf("Registered groups:\n");
            foreach (array_keys($this->groups) as $group) {
                printf("  %s\n", $group);
            }
            printf("\nSwitches:\n");
            printf("  --reset      drop all tables and views in database and start from scratch\n");
            printf("  --init-sql   prints initialization sql for all present migrations\n");
            printf("  --help       show this help\n");
            exit(intval($error));
        }
    }




    protected function createPrinter()
    {
        return new Printers\Console();
    }

}