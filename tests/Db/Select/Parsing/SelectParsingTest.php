<?php

namespace Tests\Db\Select\Parsing;

use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Engine;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Platform\Platform;
use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Db\Select\Parsing\SelectParser;
use Zend\EntityMapper\Db\Select\Reflection\OperatorReflector;
use Zend\EntityMapper\Db\Select\Reflection\SelectReflector;
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
            ->join(['b' => Engine::class], 'a.id = b.id', [])
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


        $config = $this->container->get(Engine::class);
        $configuredColumns = $config->getFields();

        $equals = 0;

        foreach ($parsedColumns as $column) {
            foreach ($configuredColumns as $field) {
                if($column == $field->getAlias()) {
                    $equals++;
                }
            }
        }

        $this->assertCount($equals, $parsedColumns);
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

        $this->assertEquals($order[0], 'hp DESC');
    }
}