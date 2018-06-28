<?php

namespace Zend\EntityMapper\Config;

use Zend\Db\Sql\Where;

/**
 * Collection
 *
 * @package Zend\EntityMapper\Config
 */
class Collection
{
    /**
     * @var string
     */
    protected $entityClass = '';

    /**
     * @var Where
     */
    protected $whereClause;

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
     * @return Where
     */
    public function getWhereClause(): Where
    {
        return $this->whereClause;
    }

    /**
     * @param Where $whereClause
     */
    public function setWhereClause(Where $whereClause)
    {
        $this->whereClause = $whereClause;
    }
}