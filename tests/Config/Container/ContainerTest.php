<?php

namespace Tests\Config\Container;

use PHPUnit\Framework\TestCase;
use Zend\EntityMapper\Config\Container\Container;

/**
 * ContainerTest
 *
 * @package Tests\Config\Container
 */
class ContainerTest extends TestCase
{
    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testDataRecovery()
    {
        $container = new Container();
        $container->set('foo', 'baar');
        $container->save();

        $container = new Container();
        $foo = $container->get('foo');

        $this->assertEquals($foo, 'baar');
    }
}