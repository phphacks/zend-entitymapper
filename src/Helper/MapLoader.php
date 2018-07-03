<?php

namespace Zend\EntityMapper\Helper;
use Zend\EntityMapper\Config\Factory\EntityConfigFactory;

/**
 * MapLoader
 *
 * @package Zend\EntityMapper\Helper
 */
class MapLoader
{
    /**
     * @var array
     */
    private $maps = [];

    /**
     * MapLoader constructor.
     *
     * @param string $directory
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function __construct(string $directory)
    {
        if(!is_dir($directory)) {
            throw new \InvalidArgumentException();
        }

        $this->load($directory);
    }

    /**
     * @param string $directory
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    private function load(string $directory): void
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
                    $this->maps[$name] = $entityConfig;
                }
            } else if(!in_array($item, $links)) {
                $this->load($path);
            }
        }

    }

    /**
     * @return array
     */
    public function getMaps(): array
    {
        return $this->maps;
    }
}