<?php

namespace Zend\EntityMapper\Db\Sql\Parsing;

use Zend\EntityMapper\Db\Sql\Parsing\Base\AbstractObjectParser;

/**
 * UpdateParser
 *
 * @package Zend\EntityMapper\Db\Sql\Parsing
 */
class UpdateParser extends AbstractObjectParser
{
    /**
     * @return array
     */
    public function parse(): array
    {
        return [
            'fields' => $this->parseFields(),
            'where'  => $this->parseWhere()
        ];
    }
}