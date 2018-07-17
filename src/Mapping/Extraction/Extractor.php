<?php

namespace Zend\EntityMapper\Mapping\Extraction;

use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Config\Field;
use Zend\Filter\FilterInterface;

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
     * @param bool $ignoreFilter
     * @return array
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function extract($object, $ignoreFilter = false): array
    {
        $fields = $this->getFields($object);
        $reflection = new \ReflectionObject($object);
        $array = [];

        foreach ($fields as $field) {
            if(!$field->isForeignKey() && !$field->isCollection()){
                $property = $reflection->getProperty($field->getProperty());
                $property->setAccessible(true);

                $value = $property->getValue($object);

                if($field->hasOutputFilter() && $ignoreFilter === false) {
                    /** @var FilterInterface $outputFilter */
                    $outputFilter = $field->getOutputFilter();
                    if(is_string($outputFilter) && class_exists($outputFilter)) {
                        $outputFilter = new $outputFilter;
                    }

                    $value = $outputFilter->filter($value);
                }

                $array[$field->getAlias()] = $value;
            }
        }

        return $array;
    }
}
