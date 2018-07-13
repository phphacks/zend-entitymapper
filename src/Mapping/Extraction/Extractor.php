<?php

namespace Zend\EntityMapper\Mapping\Extraction;

use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Config\Field;

/**
 * Extractor
 *
 * @package Zend\EntityMapper\Mapping\Extraction
 */
class Extractor
{
    /**
     * @param $object
     * @return Entity
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    private function getConfiguration($object): Entity
    {
        $container = new Container();
        $type = get_class($object);
        $configuration = $container->get($type);

        return $configuration;
    }

    /**
     * @param $object
     * @return Field[]
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    private function getFields($object): array
    {
        $configuration = $this->getConfiguration($object);
        return $configuration->getFields();
    }

    /**
     * @param $object
     * @return array
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function extract($object): array
    {
        $fields = $this->getFields($object);
        $reflection = new \ReflectionObject($object);
        $array = [];

        foreach ($fields as $field) {
            if(!$field->isForeignKey() && !$field->isCollection()){
                $array[$field->getAlias()] = $reflection->getProperty($field->getProperty())->getValue($object);
            }
        }

        return $array;
    }
}