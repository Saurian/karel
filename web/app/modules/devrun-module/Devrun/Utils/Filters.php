<?php

namespace Devrun\Utils;

/**
 * Class LatteFilters
 * @package Devrun\Utils
 */
class Filters
{

    /**
     * @param $data
     * @return \Latte\Runtime\Html
     * @throws \Nette\Utils\JsonException
     */
    public static function json($data): \Latte\Runtime\Html
    {
        return new \Latte\Runtime\Html(\Nette\Utils\Json::encode($data));
    }

}