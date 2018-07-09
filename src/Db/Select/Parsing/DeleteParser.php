<?php

namespace Zend\EntityMapper\Db\Select\Parsing;

use Zend\EntityMapper\Db\Select\Parsing\Base\AbstractObjectParser;

/**
 * UpdateParser
 *
 * @package Zend\EntityMapper\Db\Select\Parsing
 */
class DeleteParser extends AbstractObjectParser
{
    /**
     * @return array
     */
    public function parse(): array
    {
        return $this->parseWhere();
    }
}