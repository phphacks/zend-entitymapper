<?php

namespace Zend\EntityMapper\Db\Select\Parsing;

use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;

/**
 * InsertParser
 *
 * @package Zend\EntityMapper\Db\Select\Parsing
 */
class InsertParser
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
    public function parse(): array
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
}