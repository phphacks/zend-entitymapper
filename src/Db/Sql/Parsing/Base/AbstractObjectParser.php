<?php

namespace Zend\EntityMapper\Db\Sql\Parsing\Base;

use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;

abstract class AbstractObjectParser
{
    /**
     * @var Entity
     */
    private $config;

    /**
     * @var
     */
    private $object;

    /**
     * InsertParser constructor.
     *
     * @param Container $container
     * @param $object
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function __construct(Container $container, $object)
    {
        $this->config = $container->get(get_class($object));
        $this->object = $object;
    }

    /**
     * @return array
     */
    protected function parseFields(): array
    {
        $reflection = new \ReflectionObject($this->object);
        $cofigurationFields = $this->config->getFields();
        $parsed = [];

        foreach ($cofigurationFields as $cofigurationField) {
            if($cofigurationField->isCollection() || $cofigurationField->isForeignKey()) {
                continue;
            }

            $reflectionProperty = $reflection->getProperty($cofigurationField->getProperty());
            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue($this->object);

            $parsed[$cofigurationField->getAlias()] = $value;
        }

        return $parsed;
    }

    /**
     * @return array
     */
    protected function parseWhere(): array
    {
        $where = [];

        $reflection = new \ReflectionObject($this->object);
        $cofigurationFields = $this->config->getFields();

        foreach ($cofigurationFields as $cofigurationField) {
            if($cofigurationField->isPrimaryKey()) {
                $alias = $cofigurationField->getAlias();
                $reflectionProperty = $reflection->getProperty($cofigurationField->getProperty());
                $reflectionProperty->setAccessible(true);
                $value = $reflectionProperty->getValue($this->object);
                $where[$alias] = $value;
            }
        }

        return $where;
    }
}