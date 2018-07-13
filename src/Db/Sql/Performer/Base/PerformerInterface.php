<?php

namespace Zend\EntityMapper\Db\Sql\Performer\Base;

use Zend\Db\TableGateway\TableGatewayInterface;

/**
 * Interface PerformerInterface
 *
 * @package Zend\EntityMapper\Db\Sql\Performer
 */
interface PerformerInterface
{
    /**
     * @return TableGatewayInterface
     */
    public function getTableGateway(): TableGatewayInterface;

    /**
     * @param $command
     */
    public function perform($command);
}