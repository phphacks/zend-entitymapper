<?php

use Tests\Mapping\Hydration\Engine;
use Zend\EntityMapper\Helper\MapNamer;

return [
    Engine::class => [
        'schema' => 'Vehicle',
        'table'  => 'Engine',
        'fields' => [
            ['property' => 'pistons'],
            ['property' => 'cm3'],
            ['property' => 'horsepower']
        ]
    ]
];