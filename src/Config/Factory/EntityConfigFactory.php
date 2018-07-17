<?php

namespace Zend\EntityMapper\Config\Factory;

use Zend\Config\Config;
use Zend\Db\Sql\TableIdentifier;
use Zend\EntityMapper\Config\Collection;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Config\Exceptions\ConfigurationException;
use Zend\EntityMapper\Config\Field;
use Zend\EntityMapper\Config\ForeignKey;

/**
 * ConfigFactory
 *
 * @package Zend\EntityMapper\Config\Factory
 *
 */
class EntityConfigFactory
{
    /**
     * @var array
     */
    private $configArray = [];

    /**
     * @var Entity
     */
    private $config;

    /**
     * EntityConfigFactory constructor.
     *
     * @param array $configArray
     * @throws ConfigurationException
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct(array $configArray)
    {
        $this->configArray = $configArray;
        $this->config = new Entity();
        $this->mergeEntity();
    }

    /**
     * @return Entity
     */
    public function getConfig(): Entity
    {
        return $this->config;
    }

    /**
     * @throws ConfigurationException
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function mergeEntity(): void
    {
        if (!isset($this->configArray['table'])) {
            throw new ConfigurationException('No table was defined.');
        }

        if (!isset($this->configArray['schema'])) {
            throw new ConfigurationException('No schema was defined.');
        }

        $tableName = $this->configArray['table'];
        $schema = $this->configArray['schema'];
        $this->config->setTable(new TableIdentifier($tableName, $schema));

        if (!isset($this->configArray['fields']) || count($this->configArray['fields']) == 0) {
            throw new ConfigurationException('No fields defined.');
        }

        foreach ($this->configArray['fields'] as $fieldConfig) {
            $this->mergeField($fieldConfig);
        }
    }

    /**
     * @param array $fieldConfig
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function mergeField(array $fieldConfig)
    {
        $field = new Field();
        $config = new Config($fieldConfig);

        /* setup field */
        $field->setPrimaryKey($config->get('primaryKey', false));
        $field->setProperty($config->get('property', ''));
        $field->setAlias($config->get('alias', ''));
        $field->setInputFilter($config->get('inputFilter', ''));
        $field->setOutputFilter($config->get('outputFilter', ''));

        /* Check for foreign key configurations */
        $fkConfig = $config->get('foreignKey');
        if(!empty($fkConfig)) {
            $this->mergeForeignKey($fkConfig, $field);
        }

        /* Check for collection configuration */
        $collectionConfig = $config->get('collection');
        if(!empty($collectionConfig)) {
            $this->mergeCollection($collectionConfig, $field);
        }

        /* Add field to configuration */
        $this->config->setField($field->getProperty(), $field);
    }

    /**
     * @param Config $fkConfig
     * @param Field $field
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function mergeForeignKey(Config $fkConfig, Field &$field)
    {
        $foreignKey = new ForeignKey();

        $foreignKey->setTable($fkConfig->get('table'));
        $foreignKey->setEntityClass($fkConfig->get('entityClass', ''));
        $foreignKey->setJoinClause($fkConfig->get('joinClause'));
        $foreignKey->setJoinAlias($fkConfig->get('joinAlias'));

        $field->setForeignKey($foreignKey);
    }

    /**
     * @param array $collectionConfig
     * @param Field $field
     */
    public function mergeCollection(array $collectionConfig, Field &$field)
    {
        $collectionConfig = new Config($collectionConfig);
        $collection = new Collection();

        $collection->setEntityClass($collectionConfig->get('entityClass'));
        $collection->setWhereClause($collectionConfig->get('whereClause'));

        $field->setCollection($collection);
    }
}