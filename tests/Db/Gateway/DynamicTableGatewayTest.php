<?php

namespace Tests\Db\Gateway;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Db\Adapter\Driver\Mysqli\Result;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Select;
use Zend\EntityMapper\Db\Gateway\DynamicTableGateway;
use Zend\EntityMapper\Helper\MapLoader;

class SelectContainer
{
    public static $sql;
}

class StatementContainer implements StatementContainerInterface {

    private $sql;
    private $container;

    public function setSql($sql)
    {
        SelectContainer::$sql = $sql;
    }

    /**
     * Get sql
     *
     * @return mixed
     */
    public function getSql()
    {
        return SelectContainer::$sql;
    }

    /**
     * Set parameter container
     *
     * @param ParameterContainer $parameterContainer
     * @return mixed
     */
    public function setParameterContainer(ParameterContainer $parameterContainer)
    {
        $this->container = $parameterContainer;
    }

    /**
     * Get parameter container
     *
     * @return mixed
     */
    public function getParameterContainer()
    {
        return $this->container;
    }

    public function execute()
    {
        return [];
    }
}


/**
 * DynamicTableGatewayTest
 *
 * @package Tests\Db\Gateway
 */
class DynamicTableGatewayTest extends TestCase
{
    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function setUp()
    {
        $mapLoader = new MapLoader();
        $mapLoader->load(__DIR__ . '/../../resources/maps');
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testSelect()
    {
        $driver = (new MockBuilder($this, DriverInterface::class))->disableOriginalConstructor()->getMock();
        $driver->method('createStatement')->willReturn(new StatementContainer());
        $adapter = new Adapter($driver);

        $dynamicTableGateway = new DynamicTableGateway($adapter);
        $dynamicTableGateway->select(Car::class, function(Select $select){
            $select->columns(['engine.horsepower']);
            $select->where->equalTo('engine.horsepower', 300);
        });

        $select = SelectContainer::$sql;

        $this->assertNotNull($select);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testInsert()
    {
        $lastInsertId = 19950405;

        $connection = (new MockBuilder($this, Connection::class))->getMock();
        $connection->method('getLastGeneratedValue')->willReturn($lastInsertId);

        $result = (new MockBuilder($this, Result::class))->getMock();
        $result->method('getAffectedRows')->willReturn(1);

        $statementContainer = (new MockBuilder($this, StatementContainer::class))->getMock();
        $statementContainer->method('execute')->willReturn($result);

        $driver = (new MockBuilder($this, DriverInterface::class))->disableOriginalConstructor()->getMock();
        $driver->method('createStatement')->willReturn($statementContainer);
        $driver->method('getConnection')->willReturn($connection);

        $adapter = new Adapter($driver);

        $engine = new Engine();
        $engine->cm3 = 3;
        $engine->pistons = 6;
        $engine->horsepower = 300;

        $dynamicTableGateway = new DynamicTableGateway($adapter);
        $dynamicTableGateway->insert($engine);

        $this->assertEquals($lastInsertId, $engine->id);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function testUpdate()
    {
        $lastInsertId = 19950405;

        $connection = (new MockBuilder($this, Connection::class))->getMock();
        $connection->method('getLastGeneratedValue')->willReturn($lastInsertId);

        $result = (new MockBuilder($this, Result::class))->getMock();
        $result->method('getAffectedRows')->willReturn(1);

        $statementContainer = (new MockBuilder($this, StatementContainer::class))->getMock();
        $statementContainer->method('execute')->willReturn($result);

        $driver = (new MockBuilder($this, DriverInterface::class))->disableOriginalConstructor()->getMock();
        $driver->method('createStatement')->willReturn($statementContainer);
        $driver->method('getConnection')->willReturn($connection);

        $adapter = new Adapter($driver);

        $engine = new Engine();
        $engine->id = 1;
        $engine->cm3 = 3;
        $engine->pistons = 6;
        $engine->horsepower = 300;

        $dynamicTableGateway = new DynamicTableGateway($adapter);
        $dynamicTableGateway->update($engine);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function testDelete()
    {
        $lastInsertId = 19950405;

        $connection = (new MockBuilder($this, Connection::class))->getMock();
        $connection->method('getLastGeneratedValue')->willReturn($lastInsertId);

        $result = (new MockBuilder($this, Result::class))->getMock();
        $result->method('getAffectedRows')->willReturn(1);

        $statementContainer = (new MockBuilder($this, StatementContainer::class))->getMock();
        $statementContainer->method('execute')->willReturn($result);

        $driver = (new MockBuilder($this, DriverInterface::class))->disableOriginalConstructor()->getMock();
        $driver->method('createStatement')->willReturn($statementContainer);
        $driver->method('getConnection')->willReturn($connection);

        $adapter = new Adapter($driver);

        $engine = new Engine();
        $engine->id = 1;
        $engine->cm3 = 3;
        $engine->pistons = 6;
        $engine->horsepower = 300;

        $dynamicTableGateway = new DynamicTableGateway($adapter);
        $dynamicTableGateway->delete($engine);
    }
}