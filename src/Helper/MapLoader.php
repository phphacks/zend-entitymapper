<?php

namespace Zend\EntityMapper\Helper;

use Zend\Cache\StorageFactory;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Config\Factory\EntityConfigFactory;

/**
 * MapLoader
 *
 * @package Zend\EntityMapper\Helper
 */
class MapLoader
{
    /**
     * @var Container
     */
    private $maps;

    /**
     * MapLoader constructor.
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct()
    {
        $this->maps = new Container();
    }

    /**
     * @param string $directory
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function load(string $directory): void
    {
        $items = scandir($directory);
        $links = ['..', '.'];

        foreach ($items as $item) {
            $path = $directory . '/'. $item;

            if(is_file($path)) {
                $configArray = include $path;
                foreach ($configArray as $name => $config) {
                    $entityConfigFactory = new EntityConfigFactory($config);
                    $entityConfig = $entityConfigFactory->getConfig();

                    if(!$this->maps->has($name)) {
                        $this->maps->set($name, $entityConfig);
                        $this->maps->save();
                    }
                }
            } else if(!in_array($item, $links)) {
                $this->load($path);
            }
        }
    }

    /**
     * @return Container
     */
    public function getMaps(): Container
    {
        return $this->maps;
    }
}