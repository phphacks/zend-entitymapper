<?php

namespace Tests\Db\Select\Parsing;

use PHPUnit\Framework\TestCase;
use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\Db\Sql\Predicate\In;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Db\Select\Parsing\InsertParser;
use Zend\EntityMapper\Helper\MapLoader;

/**
 * Class InsertParsingTest
 *
 * @package Tests\Db\Select\Parsing
 */
class InsertParsingTest extends TestCase
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
    public function testInsertParsing()
    {
        $car = new Car();
        $car->engine = new Engine();
        $car->brand = 'Volkswagen';
        $car->model = ' Jetta';

        $insertParser = new InsertParser($this->container, $car);
        $insertParser->parse();

        $engine = new Engine();
        $engine->horsepower = 250;
        $engine->pistons = 5;
        $engine->cm3 = 3000;

        $insertParser = new InsertParser($this->container, $engine);
        $engineArray = $insertParser->parse();

        $this->assertArrayHasKey('hp', $engineArray);
    }
}