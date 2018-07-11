<?php

use Tests\Mapping\Hydration\Car;
use Tests\Mapping\Hydration\Engine;
use Zend\EntityMapper\Helper\MapNamer;

return [
    Car::class => [
        'schema' => 'Vehicle',
        'table'  => 'Car',
        'fields' => [
            ['property' => 'brand'],
            ['property' => 'model'],
            ['property' => 'engine',
                'foreignKey' => [
                    'table'       => new \Zend\Db\Sql\TableIdentifier('Engine', 'Vehicle'),
                    'entityClass' => Engine::class,
                    'joinClause'  => 'clause',
                    'joinAlias'   => 'engine'
                ]
            ]
        ]
    ]
];

