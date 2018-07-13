<?php

namespace Zend\EntityMapper\Db\Sql\Performer\Base;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\TableGatewayInterface;

/**
 * AbstractPerformer
 *
 * @package Zend\EntityMapper\Db\Sql\Performer\Base
 */
abstract class AbstractPerformer implements PerformerInterface
{
    /**
     * @var TableGateway
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