<?php

namespace Zend\EntityMapper\Db\Sql\Parsing;

use Zend\EntityMapper\Db\Sql\Parsing\Base\AbstractObjectParser;

/**
 * InsertParser
 *
 * @package Zend\EntityMapper\Db\Sql\Parsing
 */
class InsertParser extends AbstractObjectParser
{
    /**
     * @return array
     */
    public function parse(): array
    {
        return $this->parseFields();
    }
}