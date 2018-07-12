<?php

namespace Zend\EntityMapper\Db\Sql\Factory\Select;

use Zend\Db\Sql\Select;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;

/**
 * SelectFactory
 *
 * @package Zend\EntityMapper\Db\Sql\Factory\Select
 */
class SelectSkeletonFactory implements SelectFactoryInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var Entity
     */
    private $configuration;

    /**
     * @var Select
     */
    private $select;

    /**
     * SelectSkeletonFactory constructor.
     *
     * @param Container $container
     * @param string $entity
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function __construct(Container $container, string $entity)
    {
        $this->container     = $container;
        $this->entity        = $entity;
        $this->configuration = $this->container->get($entity);

        $this->select = new Select();
        $this->select->from($entity);
    }

    /**
     * @return void
     */
    public function setUpColumns(): void
    {
        $this->select->columns([]);
    }

    /**
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function setUpJoins(): void
    {
        $initialEntity = $this->entity;
        $configuration = $this->container->get($this->entity);
        $fields = $configuration->getFields();

        foreach ($fields as $field) {
            if($field->isForeignKey()) {
                // Adiciona o join
                $joinClause = $field->getForeignKey()->getJoinClause();
                $joinTable = $field->getForeignKey()->getTable();
                $joinAlias = $field->getForeignKey()->getJoinAlias();
                $this->select->join([$joinAlias => $joinTable], $joinClause, [], Select::JOIN_LEFT);

                // Implementa recursividade
                $this->entity = $field->getForeignKey()->getEntityClass();
                $this->setUpJoins();
            }
        }

        $this->entity = $initialEntity;
    }

    /**
     * @return Select
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function create(): Select
    {
        $this->setUpColumns();
        $this->setUpJoins();

        return $this->select;
    }
}