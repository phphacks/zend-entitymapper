<?php

namespace Zend\EntityMapper\Db\Select\Parsing;

use Zend\EntityMapper\Db\Select\Parsing\Base\AbstractObjectParser;

/**
 * InsertParser
 *
 * @package Zend\EntityMapper\Db\Select\Parsing
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