<?php
/**
 * This file is part of karl-von-bahnhof.
 * Copyright (c) 2018
 *
 * @file    FlashMessageControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule\Controls;

use Flame\Application\UI\Control;

interface IFlashMessageControlFactory
{
    /** @return FlashMessageControl */
    function create();
}

class FlashMessageControl extends Control
{

    const TOAST_TYPE = 'toast';

    const TOAST_INFO = [
        'hideAfter' => 40000, 'bgColor' => '#5050A0',
    ];
    const TOAST_SUCCESS = [
        'hideAfter' => 60000, 'bgColor' => '#50A050',
    ];
    const TOAST_CAMPAIGN_EDIT_SUCCESS = [
        'hideAfter' => 5000, 'bgColor' => '#50A050', 'icon' => 'success', 'showHideTransition' => 'slide', 'loader' => false, 'loaderBg' => '#9EC600',
    ];
    const TOAST_DEVICE_EDIT_SUCCESS = [
        'hideAfter' => 5000, 'bgColor' => '#50A050', 'icon' => 'info', 'showHideTransition' => 'slide', 'loader' => false, 'loaderBg' => '#9EC600',
    ];
    const TOAST_DANGER = [
        'hideAfter' => 'false', 'bgColor' => '#B03030', 'textColor' => '#F0F000', 'icon' => 'warning',
    ];
    const TOAST_CRITICAL = [
        'hideAfter' => 'false', 'bgColor' => '#B03030', 'textColor' => '#F0F000', 'icon' => 'error',
    ];
    const TOAST_WARNING = [
        'hideAfter' => 60000, 'bgColor' => '#B0A030', 'textColor' => '#F0F000', 'icon' => 'warning',
    ];


    public function render()
    {
        $template = $this->getTemplate();

        dump($this);


        $template->flashes = $this->parent->template->flashes;
        dump($template->flashes);

        $template->render();

    }


    public function renderToast()
    {
        $template = $this->getTemplate();
        $template->flashes = $this->parent->template->flashes;

        $template->setFile(__DIR__ . '/FlashToastMessage.latte')->render();
    }


}