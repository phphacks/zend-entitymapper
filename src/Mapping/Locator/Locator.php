<?php

namespace Zend\EntityMapper\Mapping;

use Zend\EntityMapper\Config\Entity as EntityConfig;
use Zend\EntityMapper\Config\Factory\EntityConfigFactory;
use Zend\EntityMapper\Mapping\Exceptions\MappingException;
use Zend\EntityMapper\Mapping\Locator\Interfaces\LocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Locator
 *
 * @package Zend\EntityMapper\Mapping
 */
class Locator implements LocatorInterface
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Locator constructor.
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param string $className
     * @return EntityConfig
     * @throws MappingException
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function locateConfigFor(string $className): EntityConfig
    {
        $configName = $className . '.map';

        if (!$this->serviceManager->has($configName)) {
            throw new MappingException("No map configuration found for '$className'.");
        }

        $config = $this->serviceManager->get($configName);
        $factory = new EntityConfigFactory($config);
        $entityConfig = $factory->getConfig();

        return $entityConfig;
    }
}