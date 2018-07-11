<?php

namespace Zend\EntityMapper\Db\Sql\Parsing;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Config\ForeignKey;
use Zend\EntityMapper\Db\Sql\Reflection\JoinReflector;
use Zend\EntityMapper\Db\Sql\Reflection\OperatorReflector;
use Zend\EntityMapper\Db\Sql\Reflection\SelectReflector;

/**
 * SelectParser
 *
 * @package Zend\EntityMapper\Db\Sql\Parsing
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
     * @var array
     */
    private $fieldAliases;

    /**
     * SelectParser constructor.
     * @param Select $select
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
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

        $this->loadFieldAliases();

        echo null;
    }

    /**
     * @param null $entity
     * @param null $namespace
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function loadFieldAliases($entity = null, $namespace = null, $fk = null): void
    {
        if (is_null($entity)) {
            $entity = $this->entity;
        }

        $joinAlias = null;
        if ($fk instanceof ForeignKey) {
            $joinAlias = $fk->getJoinAlias() . '.';
        }

        $config = $this->container->get($entity);
        $fields = $config->getFields();

        foreach ($fields as $field) {

            if(empty($namespace)) {
                $namespace = $field->getProperty();
            }
            else {
                $namespace .= '.' . $field->getProperty();
            }

            if($field->isForeignKey()) {
                $entity = $field->getForeignKey()->getEntityClass();
                $this->loadFieldAliases($entity, $namespace, $field->getForeignKey());
            }
            else if(!$field->isCollection()) {
                $this->fieldAliases[$namespace] = $joinAlias . $field->getAlias();
            }

            $namespace = null;
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
     */
    public function parseColumns(): Select
    {
        $columns = $this->reflector->getColumns();

        foreach ($columns as $alias => $column) {
            if(isset($this->fieldAliases[$column])) {
                $columns[$alias] = $this->fieldAliases[$column];
            }
        }

        $this->reflector->setColumns($columns);

        return $this->reflector->getSelect();
    }

    /**
     * @return Select
     */
    public function parseWhere(): Select
    {
        $predicates = $this->reflector->getWherePredicates();

        foreach ($predicates as $predicate) {
            $reflection = new OperatorReflector($predicate[1]);
            foreach ($this->fieldAliases as $property => $column) {
                $reflection->replaceIdentifier($property, $column);
            }
        }

        return $this->reflector->getSelect();
    }

    /**
     * @return Select
     */
    public function parseOrder(): Select
    {
        $orders = $this->reflector->getOrder();
        $parsedOrders = [];

        foreach ($orders as $order) {
            foreach ($this->fieldAliases as $property => $column) {
                str_replace($property, $column, $order);
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