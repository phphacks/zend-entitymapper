<?php

namespace Zend\EntityMapper\Config\Enums;

/**
 * PropertyConfigs
 *
 * @package Zend\EntityMapper\Enums
 */
interface PropertyConfigs
{
    /**
     * In-class property name
     */
    const PROPERTY = 'property';

    /**
     * In-Database column name
     */
    const ALIAS = 'alias';

    /**
     * Foreign key definition.
     */
    const FOREIGN_KEY = 'foreignKey';

    /**
     * Define if the field is a primaryKey (boolean)
     */
    const PRIMARY_KEY = 'primaryKey';
}