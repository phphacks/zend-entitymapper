<?php

namespace Zend\EntityMapper\Config\Container;

use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException;
use Zend\EntityMapper\Config\Entity;

/**
 * Container
 *
 * @package Zend\EntityMapper\Config\Container
 */
class Container
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $name;

    /**
     * Container constructor.
     *
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct()
    {
        $this->name = md5(__NAMESPACE__);
        $this->storage = StorageFactory::adapterFactory('filesystem');

        if ($this->storage->hasItem($this->name)) {
           $item = $this->storage->getItem($this->name);
           $this->container = unserialize($item);
        }
    }

    /**
     * @param mixed $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->container[$id]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws ItemNotFoundException
     */
    public function get($id): Entity
    {
        if (!$this->has($id)) {
            throw new ItemNotFoundException("$id not found.");
        }

        return $this->container[$id];
    }

    /**
     * @param $id
     * @param $value
     */
    public function set($id, $value)
    {
        $this->container[$id] = $value;
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function save(): void
    {
        $this->storage->setItem($this->name, serialize($this->container));
    }
}