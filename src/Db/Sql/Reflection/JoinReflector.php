<?php

namespace Zend\EntityMapper\Db\Sql\Reflection;

use Zend\Db\Sql\Join;

/**
 * JoinReflector
 *
 * @package Zend\EntityMapper\Db\Sql\Reflection
 */
class JoinReflector
{
    private $join;

    /**
     * JoinReflector constructor.
     *
     * @param Join $join
     */
    public function __construct(Join $join)
    {
        $this->join = $join;
    }

    /**
     * @return mixed
     */
    public function getJoinClauses()
    {
        $joinReflection = new \ReflectionObject($this->join);
        $joinsPropertyReflection = $joinReflection->getProperty('joins');
        $joinsPropertyReflection->setAccessible(true);
        $joinClauses = $joinsPropertyReflection->getValue($this->join);

        return $joinClauses;
    }
}