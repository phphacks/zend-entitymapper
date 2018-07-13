<?php

namespace Zend\EntityMapper\Db\Sql\Performer\Base;

use Zend\Db\TableGateway\TableGatewayInterface;

/**
 * AbstractPerformer
 *
 * @package Zend\EntityMapper\Db\Sql\Performer\Base
 */
abstract class AbstractPerformer implements PerformerInterface
{
    /**
     * @var TableGatewayInterface
     */
    protected $tableGateway;

    /**
     * @return TableGatewayInterface
     */
    public function getTableGateway(): TableGatewayInterface
    {
        return $this->tableGateway;
    }
}