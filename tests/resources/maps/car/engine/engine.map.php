<?php

use Tests\Mapping\Hydration\Engine;
use Zend\EntityMapper\Config\Enums\EntityConfigs as E;
use Zend\EntityMapper\Config\Enums\PropertyConfigs as P;

return [
    Engine::class => [
        E::SCHEMA => 'Vehicle',
        E::TABLE  => 'Engine',
        E::FIELDS => [
            [P::PROPERTY => 'id', 'primaryKey' => true],
            [P::PROPERTY => 'pistons'],
            [P::PROPERTY => 'cm3'],
            [P::PROPERTY => 'horsepower', P::ALIAS => 'hp']
        ]
    ]
];