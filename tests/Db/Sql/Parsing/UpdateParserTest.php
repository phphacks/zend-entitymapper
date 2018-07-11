<?php

namespace Zend\EntityMapper\Db\Sql\Parsing;

use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Helper\MapLoader;

/**
 * UpdateParserTest
 *
 * @package Zend\EntityMapper\Db\Sql\Parsing
 */
class UpdateParserTest extends TestCase
{
    private $container;

    /**
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Exceptions\ConfigurationException
     */
    public function setUp()
    {
        $mapLoader = new MapLoader();
        $mapLoader->load(__DIR__ . '/../../../resources/maps');

        $this->container = new Container();
    }

    /**
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function testUpdateParsing()
    {
        $car = new Car();
        $car->engine = new Engine();
        $car->brand = 'Volkswagen';
        $car->model = ' Jetta';

        $updateParser = new UpdateParser($this->container, $car);
        $updateParser->parse();

        $engine = new Engine();
        $engine->id = 1;
        $engine->horsepower = 250;
        $engine->pistons = 5;
        $engine->cm3 = 3000;

        $updateParser = new UpdateParser($this->container, $engine);
        $engineArray = $updateParser->parse();

        $this->assertCount(4, $engineArray['fields']);
        $this->assertCount(1, $engineArray['where']);
    }
}