<?php


namespace CmsModule\Controls;

use Flame\Application\UI\Control;

interface IPlayListControlFactory
{
    /** @return PlayListControl */
    function create();
}

class PlayListControl extends Control
{


    public function render()
    {
        $template = $this->getTemplate();
        $template->render();
    }



}