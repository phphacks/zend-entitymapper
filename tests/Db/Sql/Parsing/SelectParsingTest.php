<?php

namespace Tests\Db\Sql\Parsing;

use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Db\Sql\Parsing\SelectParser;
use Zend\EntityMapper\Db\Sql\Reflection\SelectReflector;
use Zend\EntityMapper\Helper\MapLoader;

/**
 * SelectParsingTest
 *
 * @package Tests\Db\Select\Parsing
 */
class SelectParsingTest extends TestCase
{
    /**
     * @var Select
     */
    private $select;

    /**
     * @var Container
     */
    private $container;

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function setUp()
    {
        $sql = new Select();
        $sql->columns(['hpower' => 'horsepower', 'pistons'])
            ->from(['a' => Engine::class])
            ->join(['b' => Engine::class], 'a.horsepower = b.horsepower', ['horsepower'])
            ->where
                ->greaterThan('horsepower', 200)
                ->and->lessThan('cm3', 2.0);
        $sql->order('horsepower DESC');
        $sql->order('roberval ASC');

        $this->select = $sql;
        $this->container = new Container();

        $mapLoader = new MapLoader();
        $mapLoader->load(__DIR__ . '/../../../resources/maps');
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testParsingTableFrom()
    {
        $config = $this->container->get(Engine::class);

        $parser = new SelectParser($this->select);
        $select = $parser->parseFrom();

        $reflector = new SelectReflector($select);

        $configuredTable = $config->getTable();
        $parsedTable = $reflector->getFrom();

        $this->assertEquals($configuredTable, $parsedTable['a']);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testParsingTableColumns()
    {
        $parser = new SelectParser($this->select);
        $parser->parseFrom();
        $select = $parser->parseColumns();

        $reflector = new SelectReflector($select);
        $parsedColumns = $reflector->getColumns();

        $this->assertCount(2, $parsedColumns);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testParsingWhereColumns()
    {
        $parser = new SelectParser($this->select);
        $parser->parseFrom();
        $parser->parseColumns();

        $select = $parser->parseWhere();

        $reflection = new \ReflectionObject($select->where);
        $predicates = $reflection->getProperty('predicates');
        $predicates->setAccessible(true);
        $predicates = $predicates->getValue($select->where);

        $horsePowerPredicate = $predicates[0][1];
        $horsePowerPredicateReflection = new \ReflectionObject($horsePowerPredicate);
        $horsePowerPredicateReflection = $horsePowerPredicateReflection->getProperty('left');
        $horsePowerPredicateReflection->setAccessible(true);
        $horsePowerPredicateIdentifier = $horsePowerPredicateReflection->getValue($horsePowerPredicate);

        $engineEntityMap = $this->container->get(Engine::class);
        $horsePowerField = $engineEntityMap->getField('horsepower');
        $horsePowerAlias = $horsePowerField->getAlias();

        $this->assertEquals($horsePowerPredicateIdentifier, $horsePowerAlias);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testParsingOrderColumns()
    {
        $parser = new SelectParser($this->select);
        $parser->parseFrom();
        $parser->parseColumns();
        $parser->parseWhere();
        $select = $parser->parseOrder();

        $reflection = new \ReflectionObject($select);
        $order = $reflection->getProperty('order');
        $order->setAccessible(true);
        $order = $order->getValue($select);

        $this->assertEquals($order[0], 'horsepower DESC');
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testParsingJoinClauses()
    {
        $parser = new SelectParser($this->select);
        $parser->parseFrom();
        $parser->parseColumns();
        $parser->parseWhere();
        $parser->parseFrom();

        $select = $parser->parseJoin();

        $selectReflection = new \ReflectionObject($select);
        $joinsProperty = $selectReflection->getProperty('joins');
        $joinsProperty->setAccessible(true);
        $joins = $joinsProperty->getValue($select);

        $joinsReflection = new \ReflectionObject($joins);
        $joinsProperty = $joinsReflection->getProperty('joins');
        $joinsProperty->setAccessible(true);
        $joinsArray = $joinsProperty->getValue($joins);

        $clause = $joinsArray[0];
        $on = $clause['on'];

        $this->assertNotEmpty($joinsArray);
        $this->assertEquals($on, 'a.hp = b.hp');
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testNamespacedColumnsSelect()
    {
        $select = new Select();
        $select->columns(['brand', 'engine.horsepower']);
        $select->from(Car::class);

        $parser = new SelectParser($select);
        $parser->parseFrom();
        $select = $parser->parseColumns();

        $this->assertInstanceOf(Select::class, $select);
    }
}