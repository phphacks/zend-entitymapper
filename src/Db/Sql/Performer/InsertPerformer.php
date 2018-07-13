<?php

namespace Zend\EntityMapper\Db\Sql\Performer;

use Zend\Db\TableGateway\TableGateway;
use Zend\EntityMapper\Db\Sql\Performer\Base\AbstractPerformer;
use Zend\EntityMapper\Mapping\Extraction\Extractor;
use Zend\EntityMapper\Mapping\Hydration\Hydrator;

/**
 * InsertPerformer
 *
 * @package Zend\EntityMapper\Db\Sql\Performer
 */
class InsertPerformer extends AbstractPerformer
{
    /**
     * InsertPerformer constructor.
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * @param $object
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function perform($object)
    {
        $extractor = new Extractor();
        $array = $extractor->extract($object);

        $this->tableGateway->insert($array);
        $lastInsertValue = $this->tableGateway->getLastInsertValue();

        $hydrator = new Hydrator();
        $hydrator->hydratePrimaryKey($lastInsertValue, $object);

        return $object;
    }
}