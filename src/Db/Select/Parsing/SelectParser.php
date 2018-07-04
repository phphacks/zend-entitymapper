<?php

namespace Zend\EntityMapper\Db\Select\Parsing;

use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
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
        $map = $this->getMap();

        $columns = [];

        foreach ($this->reflector->getColumns() as $columnAlias => $column) {
            $propertyAlias = $map->getField($column)->getAlias();
            $columns[$columnAlias] = $propertyAlias;
        }

        $this->reflector->setColumns($columns);
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
}