<?php

namespace Zend\EntityMapper\Db\Sql\Reflection;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\TableIdentifier;

/**
 * SelectReflector
 *
 * @package Zend\EntityMapper\Db\Sql\Reflection
 */
class SelectReflector
{
    /**
     * @var Select
     */
    private $select;

    /**
     * @var \ReflectionObject
     */
    private $reflection;

    /**
     * SelectReflector constructor.
     *
     * @param Select $select
     */
    public function __construct(Select $select)
    {
        $this->select = $select;
        $this->reflection = new \ReflectionObject($select);
    }

    /**
     * @return Select
     */
    public function getSelect(): Select
    {
        return $this->select;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getProperty(string $name)
    {
        $property = $this->reflection->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($this->select);
    }

    /**
     * @param string $name
     * @param $value
     */
    public function setProperty(string $name, $value): void
    {
        $property = $this->reflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->select, $value);
    }

    /**
     * @return string|array|TableIdentifier
     */
    public function getFrom()
    {
        return $this->getProperty('table');
    }

    /**
     * @param $from
     */
    public function setFrom($from): void
    {
        $this->select->from($from);
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->getProperty('columns');
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns): void
    {
        $this->select->columns($columns);
    }

    /**
     * @return array
     */
    public function getWherePredicates(): array
    {
        $where = $this->getProperty('where');
        $reflection = new \ReflectionObject($where);

        $predicates = $reflection->getProperty('predicates');
        $predicates->setAccessible(true);
        $predicates = $predicates->getValue($where);

        return $predicates;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        $order = $this->getProperty('order');
        return $order;
    }
}