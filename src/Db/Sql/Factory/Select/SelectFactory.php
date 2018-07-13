<?php

namespace Zend\EntityMapper\Db\Sql\Factory\Select;

use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Db\Sql\Parsing\SelectParser;

/**
 * SelectFactory
 *
 * @package Zend\EntityMapper\Db\Sql\Factory\Select
 */
class SelectFactory implements SelectFactoryInterface
{
    /**
     * @var string
     */
    private $entity;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array|callable|Select
     */
    private $override;

    /**
     * SelectFactory constructor.
     *
     * @param string $entity
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct(string $entity)
    {
        $this->entity = $entity;
        $this->container = new Container();
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $select
     */
    public function setOverride($select)
    {
        $this->override = $select;
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function getSelectSkeleton(): Select
    {
        return (new SelectSkeletonFactory($this->container, $this->entity))->create();
    }

    /**
     * @param Select $select
     * @return Select
     */
    private function applyOverride(Select $select): Select
    {
        if ($this->override instanceof Select) {
            $select = $select->combine($this->override);
        }
        else if (is_array($select)) {
            $select = $select->where($this->override);
        }
        else if (is_callable($this->override)) {
            $callback = $this->override;
            $callback($select);
        }

        return $select;
    }

    /**
     * @param Select $select
     * @return Select
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    private function parse(Select $select): Select
    {
        $selectParser = new SelectParser($select);
        $selectParser->parseFrom();
        $selectParser->parseColumns();
        $selectParser->parseJoin();
        $selectParser->parseWhere();
        $select = $selectParser->parseOrder();

        return $select;
    }

    /**
     * @return Select
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function create(): Select
    {
        $select = $this->getSelectSkeleton();
        $select = $this->applyOverride($select);
        $select = $this->parse($select);

        return $select;
    }
}