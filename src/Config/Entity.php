<?php

namespace Zend\EntityMapper\Config;

use Zend\Db\Sql\TableIdentifier;

class Entity
{
    /**
     * @var TableIdentifier
     */
    protected $table;

    /**
     * @var Field[]
     */
    protected $fields = [];

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
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param $id
     * @return Field
     */
    public function getField($id): Field
    {
        return $this->fields[$id];
    }

    /**
     * @param $id
     * @param Field $field
     */
    public function setField($id, Field $field)
    {
        $this->fields[$id] = $field;
    }
}