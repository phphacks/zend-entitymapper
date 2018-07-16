<?php

namespace Tests\Db\Gateway;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli;
use Zend\Db\Adapter\Driver\Mysqli\Result;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Platform\Mysql\Mysql;
use Zend\Db\Sql\Platform\Platform;
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
    private $result;

    public function __construct($result = null)
    {
        $this->result = $result;
    }

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
        if(!empty($this->result)) {
            return $this->result;
        }

        return [];
    }
}

class MyPlatform extends Mysql implements PlatformInterface
{
    public function getName()
    {
        return 'xablau';
    }

    /**
     * Get quote identifier symbol
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '';
    }

    /**
     * Quote identifier
     *
     * @param  string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $identifier;
    }

    /**
     * Quote identifier chain
     *
     * @param string|string[] $identifierChain
     * @return string
     */
    public function quoteIdentifierChain($identifierChain)
    {
        return '';
    }

    /**
     * Get quote value symbol
     *
     * @return string
     */
    public function getQuoteValueSymbol()
    {
        return '';
    }

    /**
     * Quote value
     *
     * Will throw a notice when used in a workflow that can be considered "unsafe"
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value)
    {
        return $value;
    }

    /**
     * Quote Trusted Value
     *
     * The ability to quote values without notices
     *
     * @param $value
     * @return mixed
     */
    public function quoteTrustedValue($value)
    {
        return $value;
    }

    /**
     * Quote value list
     *
     * @param string|string[] $valueList
     * @return string
     */
    public function quoteValueList($valueList)
    {
        return '';
    }

    /**
     * Get identifier separator
     *
     * @return string
     */
    public function getIdentifierSeparator()
    {
        return '';
    }

    /**
     * Quote identifier in fragment
     *
     * @param  string $identifier
     * @param  array $additionalSafeWords
     * @return string
     */
    public function quoteIdentifierInFragment($identifier, array $additionalSafeWords = [])
    {
        return '';
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
        $driver = $this->createMock(DriverInterface::class);
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

        $connection = $this->createMock(Connection::class);
        $connection->method('getLastGeneratedValue')->willReturn($lastInsertId);

        $result = $this->createMock(Result::class);
        $result->method('getAffectedRows')->willReturn(1);

        $statementContainer = $this->createMock(StatementContainer::class);
        $statementContainer->method('execute')->willReturn($result);

        $driver = $this->createMock(Mysqli::class);
        $driver->method('createStatement')->willReturn($statementContainer);
        $driver->method('getConnection')->willReturn($connection);

        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->setConstructorArgs([$driver])
            ->getMock();

        $platform = $this
            ->getMockBuilder(MyPlatform::class)
            ->setConstructorArgs([$adapter])
            ->getMock();

        $platform->method('prepareStatement')
            ->willReturn($statementContainer);

        $adapter->method('getPlatform')
            ->willReturn($platform);

        $adapter->method('getDriver')
            ->willReturn($driver);

        $engine = new Engine();
        $engine->cm3 = 3;
        $engine->pistons = 6;
        $engine->horsepower = 300;

        $dynamicTableGateway = new DynamicTableGateway($adapter);
        $dynamicTableGateway->insert($engine);

        $this->assertEquals($lastInsertId, $engine->id);
    }
}