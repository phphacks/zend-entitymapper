<?php

namespace Zend\EntityMapper\Mapping\Hydration;

use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Mapping\Hydration\Interfaces\HydratorInterface;
use Zend\EntityMapper\Mapping\Locator\Interfaces\LocatorInterface;
use Zend\Filter\FilterInterface;

/**
 * Hydrator
 *
 * @package Zend\EntityMapper\Mapping\Hydration
 */
class Hydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param object $object
     * @return object
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function hydrate(array $data, $object)
    {
        $container = new Container();

        /** @var Entity $config */
        $config = $container->get(get_class($object));

        $reflectionObject = new \ReflectionObject($object);
        $objectProperties = $reflectionObject->getProperties();

        foreach ($objectProperties as $property)
        {
            $propertyName = $property->getName();
            $propertyConfig = $config->getField($propertyName);
            $propertyAlias = $propertyConfig->getAlias();

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
                $inputFilterClass = $propertyConfig->getInputFilter();

                /** @var FilterInterface $inputFilter */
                $inputFilter = new $inputFilterClass;
                $filtered = $inputFilter->filter($data[$propertyAlias]);
                $property->setValue($object, $filtered);
                continue;
            }

            $property->setValue($object, $data[$propertyAlias]);
        }

        return $object;
    }
}