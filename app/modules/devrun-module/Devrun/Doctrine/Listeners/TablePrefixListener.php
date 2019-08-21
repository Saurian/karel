<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    TablePrefixListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Listeners;

use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;


class TablePrefixListener
{

    protected $_prefix = '';

    public function __construct($prefix)
    {
        $this->_prefix = (string)$prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $classMetadata->setTableName($this->_prefix . $classMetadata->getTableName());
        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->_prefix . $mappedTableName;
            }
        }

    }
}


