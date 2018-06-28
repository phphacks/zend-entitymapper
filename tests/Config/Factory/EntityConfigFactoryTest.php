<?php

namespace Tests\Config\Factory;

use PHPUnit\Framework\TestCase;
use Zend\EntityMapper\Config\Factory\EntityConfigFactory;

class EntityConfigFactoryTest extends TestCase
{
    /**
     * Entity to be mapped
     */
    const SUBJECT = 'Auth\Domain\Entity\Credential.map';

    /**
     * @var array
     */
    private $config;

    /**
     * Setup de tests.
     */
    public function setUp()
    {
        $this->config = [
            self::SUBJECT => [
                'schema' => 'Auth',
                'table'  => 'Credential',
                'fields' => [
                    [
                        'property' => 'username',
                        'alias'    => 'username'
                    ],
                    [
                        'property' => 'password',
                        'alias'    => 'password'
                    ]
                ]
            ]
        ];
    }

    /**
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function testConfigFactory()
    {
        $map = $this->config[self::SUBJECT];

        $factory = new EntityConfigFactory($map);
        $config  = $factory->getConfig();

        /* Entity config assertion */
        $this->assertEquals($config->getTable()->getSchema(), $this->config[self::SUBJECT]['schema']);
        $this->assertEquals($config->getTable()->getTable(),  $this->config[self::SUBJECT]['table']);
        $this->assertCount(2, $config->getFields());
    }
}