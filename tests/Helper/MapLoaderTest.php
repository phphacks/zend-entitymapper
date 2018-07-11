<?php

namespace Tests\Helper;

use PHPUnit\Framework\TestCase;
use Zend\Cache\StorageFactory;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Entity;
use Zend\EntityMapper\Helper\MapLoader;

/**
 * MapLoaderTest
 *
 * @package Tests\Helper
 */
class MapLoaderTest extends TestCase
{
    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function testMapsLoading()
    {
        $dir = __DIR__ . '/../resources/maps';

        $loader = new MapLoader();
        $loader->load($dir);
        $container = new Container();

        $this->assertInstanceOf(Entity::class, $container->get('Tests\Mapping\Hydration\Car'));
        $this->assertInstanceOf(Entity::class, $container->get('Tests\Mapping\Hydration\Engine'));
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function testMapsStorage()
    {
        $dir = __DIR__ . '/../resources/maps';
        $mapLoader = new MapLoader();
        $mapLoader->load($dir);
        $maps = $mapLoader->getMaps();

        $storage = StorageFactory::adapterFactory('filesystem');
        $storage->setItem('maps', serialize($maps));

        $storage = StorageFactory::adapterFactory('filesystem');
        $restoredMaps = $storage->getItem('maps');
        $restoredMaps = unserialize($restoredMaps);

        $this->assertTrue($restoredMaps->has('Tests\Mapping\Hydration\Car'));
        $this->assertTrue($restoredMaps->has('Tests\Mapping\Hydration\Engine'));
    }
}