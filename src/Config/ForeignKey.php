<?php

namespace Zend\EntityMapper\Config;

use Zend\Db\Sql\TableIdentifier;

/**
 * ForeignKey
 *
 * Foreign key configuration class.
 *
 * @package Zend\EntityMapper\Config
 */
class ForeignKey
{
    /**
     * @var string
     */
    protected $entityClass = '';

    /**
     * @var TableIdentifier
     */
    protected $table;

    /**
     * @var string
     */
    protected $joinClause;

    /**
     * @var string
     */
    protected $joinAlias;

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return TableIdentifier
     */
    public function getTable(): TableIdentifier
    {
        return $this->table;
    }

    /**
     * @param TableIdentifier $table
     */
    public function setTable(TableIdentifier $table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getJoinClause(): string
    {
        return $this->joinClause;
    }

    /**
     * @param string $joinClause
     */
    public function setJoinClause(string $joinClause)
    {
        $this->joinClause = $joinClause;
    }

    /**
     * @return string
     */
    public function getJoinAlias(): string
    {
        return $this->joinAlias;
    }

    /**
     * @param string $joinAlias
     */
    public function setJoinAlias(string $joinAlias)
    {
        $this->joinAlias = $joinAlias;
    }
}