<?php

namespace Zend\EntityMapper\Db\Select\Reflection;

use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Where;

/**
 * WhereReflector
 *
 * @package Zend\EntityMapper\Db\Select\Reflection
 */
class OperatorReflector
{
    /**
     * @var Operator
     */
    private $operator;

    /**
     * @var \ReflectionObject
     */
    private $reflection;

    /**
     * OperatorReflector constructor.
     *
     * @param Operator $operator
     */
    public function __construct(Operator $operator)
    {
        $this->operator = $operator;
        $this->reflection = new \ReflectionObject($operator);
    }

    /**
     * @return array
     */
    public function getIdentifiers(): array
    {
        $identifiers = [];

        $leftType = $this->reflection->getProperty('leftType');
        $leftType->setAccessible(true);
        $leftType = $leftType->getValue($this->operator);

        $rightType = $this->reflection->getProperty('rightType');
        $rightType->setAccessible(true);
        $rightType = $rightType->getValue($this->operator);

        if($rightType == 'identifier') {
            $right = $this->reflection->getProperty('right');
            $right->setAccessible(true);
            $right = $right->getValue($this->operator);

            $identifiers[] = $right;
        }

        if($leftType == 'identifier') {
            $left = $this->reflection->getProperty('left');
            $left->setAccessible(true);
            $left = $left->getValue($this->operator);

            $identifiers[] = $left;
        }

        return $identifiers;
    }

    /**
     * @param string $from
     * @param string $to
     * @return Operator
     */
    public function replaceIdentifier(string $from, string $to): Operator
    {
        $leftType = $this->reflection->getProperty('leftType');
        $leftType->setAccessible(true);
        $leftType = $leftType->getValue($this->operator);

        $rightType = $this->reflection->getProperty('rightType');
        $rightType->setAccessible(true);
        $rightType = $rightType->getValue($this->operator);

        if($rightType == 'identifier') {
            $right = $this->reflection->getProperty('right');
            $right->setAccessible(true);
            $rightValue = $right->getValue($this->operator);

            if ($rightValue == $from) {
               $right->setValue($this->operator, $to);
            }
        }

        if($leftType == 'identifier') {
            $left = $this->reflection->getProperty('left');
            $left->setAccessible(true);
            $leftValue = $left->getValue($this->operator);

            if ($leftValue == $from) {
                $left->setValue($this->operator, $to);
            }
        }

        return $this->operator;
    }
}