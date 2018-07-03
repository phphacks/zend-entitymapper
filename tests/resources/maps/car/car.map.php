<?php

use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\EntityMapper\Helper\MapNamer;

return [
    MapNamer::getNameFor(Car::class) => [
        'schema' => 'Vehicle',
        'table'  => 'Car',
        'fields' => [
            ['property' => 'brand'],
            ['property' => 'model'],
            ['property' => 'engine',
                'foreignKey' => [
                    'table'       => ['Car', 'Vehicle'],
                    'entityClass' => Engine::class,
                    'joinClause'  => 'clause'
                ]
            ]
        ]
    ]
];