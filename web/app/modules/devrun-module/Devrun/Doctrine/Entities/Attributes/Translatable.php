<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    Translatable.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities\Attributes;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethods;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableProperties;
use Nette\Utils\Strings;

trait Translatable
{

    use TranslatableProperties,
        TranslatableMethods;



    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationEntityClass()
    {
        // By default, the translatable class has the same name but without the "Translation" suffix
        // fix problem with class has Entity postfix
        return Strings::endsWith(__CLASS__, 'Entity') ? substr(__CLASS__, 0, -6) . "TranslationEntity" : __CLASS__.'Translation';
    }


}