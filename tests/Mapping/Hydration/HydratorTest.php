<?php

namespace Tests\Mapping\Hydration;

use PHPUnit\Framework\TestCase;
use Zend\EntityMapper\Mapping\Hydration\Hydrator;

/**
 * HydratorTest
 *
 * @package Tests\Mapping\Hydration
 */
class HydratorTest extends TestCase
{
    private $engine;
    private $car;

    public function setUp()
    {
        $this->engine = ['pistons' => 8, 'cm3' => 3.2, 'hp' => 380];
        $this->car    = ['brand' => 'volkswagen', 'model' => 'touareg', 'engine' => $this->engine];
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testOneLevelHydration(): void
    {
        $hydrator = new Hydrator();
        $object = $hydrator->hydrate($this->engine, new Engine());

        $this->assertEquals($object->pistons,    $this->engine['pistons']);
        $this->assertEquals($object->cm3,        $this->engine['cm3']);
        $this->assertEquals($object->horsepower, $this->engine['hp']);
    }

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testTwoLevelsHydration()
    {
        $hydrator = new Hydrator();
        $object = $hydrator->hydrate($this->car, new Car());

        $this->assertInstanceOf(Engine::class, $object->engine);
        $this->assertEquals($object->engine->pistons,    $this->engine['pistons']);
        $this->assertEquals($object->engine->cm3,        $this->engine['cm3']);
        $this->assertEquals($object->engine->horsepower, $this->engine['hp']);
    }


}