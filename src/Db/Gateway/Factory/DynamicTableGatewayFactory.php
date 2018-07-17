<?php

namespace Zend\EntityMapper\Db\Gateway\Factory;

use Interop\Container\ContainerInterface;
use Zend\EntityMapper\Db\Gateway\DynamicTableGateway;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * DynamicTableGatewayFactory
 *
 * @package Zend\EntityMapper\Db\Gateway\Factory
 */
class DynamicTableGatewayFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object|DynamicTableGateway
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get('Zend\Db\Adapter\Adapter');
        $dynamicTableGateway = new DynamicTableGateway($adapter);

        return $dynamicTableGateway;
    }
}