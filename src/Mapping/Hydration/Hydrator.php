<?php

namespace Zend\EntityMapper\Mapping\Hydration;

use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Mapping\Hydration\Interfaces\HydratorInterface;
use Zend\Filter\FilterInterface;

/**
 * Hydrator
 *
 * @package Zend\EntityMapper\Mapping\Hydration
 */
class Hydrator implements HydratorInterface
{
    private $configs = [];
    private $inputFilters = [];

    /**
     * @param $object
     * @return null|Entity
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    private function getConfig($object)
    {
        $config = null;

        $class = $object;
        if(is_object($object)) {
            $class = get_class($object);
        }

        if(isset($this->configs[$class])) {
            /** @var Entity $config */
            $config = $this->configs[$class];
        }
        else
        {
            $container = new Container();

            /** @var Entity $config */
            $config = $container->get($class);

            $this->configs[$class] = $config;
        }

        return $config;
    }


    /**
     * @param $value
     * @param $object
     * @return mixed
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function hydratePrimaryKey($value, $object)
    {
        $config = $this->getConfig($object);

        $reflection = new \ReflectionObject($object);

        foreach ($config->getFields() as $field) {
            if($field->isPrimaryKey()) {
                $property = $reflection->getProperty($field->getProperty());
                $property->setAccessible(true);
                $property->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * @param array $data
     * @param object $object
     * @return object
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function hydrate(array $data, $object)
    {
        $config = $this->getConfig($object);

        $reflectionObject = new \ReflectionObject($object);
        $objectProperties = $reflectionObject->getProperties();

        foreach ($objectProperties as $property)
        {
            $propertyName = $property->getName();
            $propertyConfig = $config->getField($propertyName);
            $propertyAlias = $propertyConfig->getAlias();
            $property->setAccessible(true);

            // Populate foreignKey
            if ($propertyConfig->isForeignKey()) {
                $foreignKey = $propertyConfig->getForeignKey();
                $entityClass = $foreignKey->getEntityClass();
                $foreignKeyObject = $this->hydrate($data[$propertyAlias], new $entityClass);
                $property->setValue($object, $foreignKeyObject);
                continue;
            }

            // Populate Collection
            if ($propertyConfig->isCollection()) {
                continue;
            }

            // Filter the data
            if($propertyConfig->hasInputFilter()) {

                /** @var FilterInterface $outputFilter */
                $inputFilter = $propertyConfig->getInputFilter();
                if(is_string($inputFilter) && class_exists($inputFilter)) {
                    $inputFilter = new $inputFilter;
                }

                $filtered = $inputFilter->filter($data[$propertyAlias]);
                $property->setValue($object, $filtered);
                continue;
            }

            if(isset($data[$propertyAlias]))
                $property->setValue($object, $data[$propertyAlias]);
        }

        return $object;
    }
}
