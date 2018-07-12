<?php

namespace Tests\Db\Gateway;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Car;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\DriverInterface;
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
}