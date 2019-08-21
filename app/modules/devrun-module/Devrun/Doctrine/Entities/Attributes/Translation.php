<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    TranslationTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities\Attributes;

use Knp\DoctrineBehaviors\Model\Translatable\TranslationMethods;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationProperties;
use Nette\Utils\Strings;

trait Translation
{
    use TranslationProperties,
        TranslationMethods;


    /**
     * Returns the translatable entity class name.
     *
     * @return string
     */
    public static function getTranslatableEntityClass()
    {
        // By default, the translatable class has the same name but without the "Translation" suffix
        // fix problem with class has Entity postfix
        return Strings::endsWith(__CLASS__, 'Entity') ? substr(__CLASS__, 0, -17) . "Entity" : substr(__CLASS__, 0, -11);
    }


}