<?php

namespace Zend\EntityMapper\Config\Enums;

/**
 * Interface EntityConfigs
 *
 * @package Zend\EntityMapper\Config\Enums
 */
interface EntityConfigs
{
    /**
     * Schema where the table is located.
     */
    const SCHEMA = 'schema';

    /**
     * Table corresponding to the entity.
     */
    const TABLE  = 'table';

    /**
     * Entity fields definition.
     */
    const FIELDS = 'fields';
}