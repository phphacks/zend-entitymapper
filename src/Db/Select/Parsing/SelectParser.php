<?php

namespace Zend\EntityMapper\Db\Select\Parsing;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Config\ForeignKey;
use Zend\EntityMapper\Db\Select\Reflection\JoinReflector;
use Zend\EntityMapper\Db\Select\Reflection\OperatorReflector;
use Zend\EntityMapper\Db\Select\Reflection\SelectReflector;

/**
 * SelectParser
 *
 * @package Zend\EntityMapper\Db\Select\Parsing
 */
class SelectParser
{
    /**
     * @var SelectReflector
     */
    private $reflector;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $tableAlias;

    /**
     * @var Entity
     */
    private $map;

    /**
     * SelectParser constructor.
     *
     * @param Select $select
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct(Select $select)
    {
        $this->reflector = new SelectReflector($select);
        $this->container = new Container();

        $entity = $this->reflector->getFrom();

        if(is_array($entity)) {
            foreach ($entity as $alias => $namespace) {
                $this->entity = $namespace;
                $this->tableAlias = $alias;
            }
        }


        if(is_string($entity) && class_exists($entity)) {
            $this->entity = $entity;
        }

    }

    /**
     * @return mixed|Entity
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function getMap()
    {
        if(!$this->map instanceof Entity) {
            $this->map = $this->container->get($this->entity);
        }

        return $this->map;
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function parseFrom(): Select
    {
        if(!empty($this->tableAlias)) {
            $this->reflector->setFrom([
                $this->tableAlias => $this->getMap()->getTable()
            ]);
        }
        else {
            $this->reflector->setFrom($this->getMap()->getTable());
        }

        return $this->reflector->getSelect();
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function parseColumns(): Select
    {
        $fields = $this->container->get($this->entity)->getFields();
        $columns = $this->reflector->getColumns();
        $parsedColumns = [];

        foreach ($columns as $columnName) {
            $value = null;
            $levels = explode('.', $columnName);
            $config = $this->container->get($this->entity);
            $foreignKey = null;
            foreach ($levels as $column) {
                $field = $config->getField($column);

                if($field->isForeignKey()) {
                    $foreignKey = $field->getForeignKey();
                    $config = $this->container->get($foreignKey->getEntityClass());
                }
                else if($field->isCollection()) {

                }
                else
                {
                    $schema = $config->getTable()->getSchema();
                    $table = $config->getTable()->getTable();
                    $column = $field->getAlias();

                    if($foreignKey instanceof ForeignKey) {
                        $value = new Expression($foreignKey->getJoinAlias() . '.' . $column);
                    }
                    else
                    {
                        $value = new Expression($schema . '.' . $table . '.' . $column);
                    }
                }
            }

            $parsedColumns[$columnName] = $value;
        }

        $this->reflector->setColumns($parsedColumns);

        return $this->reflector->getSelect();
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function parseWhere(): Select
    {
        $predicates = $this->reflector->getWherePredicates();
        $map = $this->getMap();

        foreach ($predicates as $predicate) {
            $reflection = new OperatorReflector($predicate[1]);
            $identifiers = $reflection->getIdentifiers();
            $fields = $map->getFields();

            foreach ($fields as $field) {
                if(in_array($field->getProperty(), $identifiers)) {
                    $predicate[1] = $reflection->replaceIdentifier($field->getProperty(), $field->getAlias());
                }
            }

        }

        return $this->reflector->getSelect();
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function parseOrder(): Select
    {
        $orders = $this->reflector->getOrder();
        $parsedOrders = [];

        foreach ($orders as $order) {
            $fields = $this->getMap()->getFields();

            foreach ($fields as $field) {
                $order = str_replace($field->getProperty(), $field->getAlias(), $order);
            }

            $parsedOrders[] = $order;
        }

        $select = $this->reflector->getSelect();

        $reflection = new \ReflectionObject($select);
        $order = $reflection->getProperty('order');
        $order->setAccessible(true);
        $order->setValue($select, $parsedOrders);

        return $select;
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function parseJoin(): Select
    {
        $joins = $this->reflector->getProperty('joins');
        $joinReflector = new JoinReflector($joins);
        $joinClauses = $joinReflector->getJoinClauses();
        $parsedJoinClauses = [];

        foreach ($joinClauses as $joinClause) {

            $tableName = null;
            $rawObjectName = $joinClause['name'];

            if(is_array($rawObjectName)) {
                foreach ($rawObjectName as $alias => $class) {
                    $config = $this->container->get($class);
                    $joinClause['name'] = [$alias => $config->getTable()];
                    $fields = $config->getFields();
                }
            }
            else {
                $config = $this->container->get($rawObjectName);
                $fields = $config->getFields();
            }

            foreach ($fields as $field) {
                $joinClause['on'] = str_replace($field->getProperty(), $field->getAlias(), $joinClause['on']);

                foreach ($joinClause['columns'] as $key => $column) {
                    $joinClause['columns'][$key] = str_replace($field->getProperty(), $field->getAlias(), $joinClause['columns'][$key]);
                }
            }

            $parsedJoinClauses[] = $joinClause;

        }

        $joinsReflection = new \ReflectionObject($joins);
        $joinsReflectorJoinsProperty = $joinsReflection->getProperty('joins');
        $joinsReflectorJoinsProperty->setAccessible(true);
        $joinsReflectorJoinsProperty->setValue($joins, $parsedJoinClauses);

        $this->reflector->setProperty('joins', $joins);
        $select = $this->reflector->getSelect();

        return $select;
    }
}