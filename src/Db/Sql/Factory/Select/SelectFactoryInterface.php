<?php

namespace Zend\EntityMapper\Db\Sql\Factory\Select;

use Zend\Db\Sql\Select;

/**
 * Interface SelectFactoryInterface
 *
 * @package Zend\EntityMapper\Db\Sql\Factory\Select
 */
interface SelectFactoryInterface
{
    public function create(): Select;
}