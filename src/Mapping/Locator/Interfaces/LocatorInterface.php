<?php

namespace Zend\EntityMapper\Mapping\Locator\Interfaces;

use Zend\EntityMapper\Config\Entity as EntityConfig;

/**
 * Interface LocatorInterface
 *
 * @package Zend\EntityMapper\Mapping\Locator\Interfaces
 */
interface LocatorInterface
{
    public function locateConfigFor(string $className): EntityConfig;
}