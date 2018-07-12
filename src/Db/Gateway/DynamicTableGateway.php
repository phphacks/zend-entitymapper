<?php

namespace Zend\EntityMapper\Db\Gateway;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\Factory\TableGatewayFactory;
use Zend\Db\TableGateway\TableGateway;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Db\Sql\Factory\Select\SelectSkeletonFactory;
use Zend\EntityMapper\Db\Sql\Parsing\SelectParser;
use Zend\EntityMapper\Mapping\Hydration\ArrayBreaker;
use Zend\EntityMapper\Mapping\Hydration\Hydrator;

/**
 * DynamicTableGateway
 *
 * @package Zend\EntityMapper\Db\Gateway
 */
class DynamicTableGateway
{
    /**
     * Stores tablegateways used during the proccess.
     *
     * @var TableGateway[]
     */
    private static $tableGateways = [];

    /**
     * Stores entities configuration files.
     *
     * @var Container
     */
    private static $configurationContainer;

    /**
     * Database adapter instance.
     *
     * @var Adapter
     */
    private $adapter;

    /**
     * DatabaseGateway constructor.
     *
     * @param Adapter $adapter
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct(Adapter $adapter)
    {
        self::$configurationContainer = new Container();
        $this->adapter = $adapter;
    }

    /**
     * @param string $entity
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    private function setUp(string $entity): void
    {
        if (array_key_exists($entity, self::$tableGateways)) {
            return;
        }

        $configuration = self::$configurationContainer->get($entity);
        $tableIdentifier = $configuration->getTable();

        $tableGatewayFactory = new TableGatewayFactory($this->adapter);
        $tableGateway = $tableGatewayFactory->create($tableIdentifier->getTable(), $tableIdentifier->getSchema());
        self::$tableGateways[$entity] = $tableGateway;
    }

    /**
     * @param string $entity
     * @param $select
     * @return array
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function select(string $entity, $select)
    {
        $this->setUp($entity);

        $selectSkeletonFactory = new SelectSkeletonFactory(self::$configurationContainer, $entity);
        $selectSkeleton = $selectSkeletonFactory->create();

        if ($select instanceof Select) {
            $select = $selectSkeleton->combine($select);
        }
        else if (is_array($select)) {
            $select = $selectSkeleton->where($select);
        }
        else if (is_callable($select)) {
            $select($selectSkeleton);
            $select = $selectSkeleton;
        }

        $selectParser = new SelectParser($select);
        $selectParser->parseFrom();
        $selectParser->parseColumns();
        $selectParser->parseJoin();
        $selectParser->parseWhere();
        $select = $selectParser->parseOrder();

        $rowsToBeShown = [];
        $rows = self::$tableGateways[$entity]->selectWith($select);
        $hydrator = new Hydrator();

        foreach ($rows as $row) {
            $brokenRow = ArrayBreaker::break($row);
            $subject = new $entity;
            $rowsToBeShown[] = $hydrator->hydrate($brokenRow, $subject);
        }

        return $rowsToBeShown;
    }
}