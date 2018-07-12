<?php

namespace Tests\Mapping;

use PHPUnit\Framework\TestCase;
use Zend\EntityMapper\Mapping\Hydration\ArrayBreaker;

/**
 * ArrayBreakerTest
 *
 * @package Tests\Mapping
 */
class ArrayBreakerTest extends TestCase
{
    public function testArrayBreaking()
    {
        $toBeBroken = [
            'name'                  => 'Jetta',
            'brand'                 => 'Volkswagen',
            'engine.cv'             => 300,
            'engine.turbo.pressure' => 0.6
        ];

        $broken = ArrayBreaker::break($toBeBroken);

        $this->assertEquals($broken['name'], $toBeBroken['name']);
        $this->assertEquals($broken['brand'], $toBeBroken['brand']);
        $this->assertEquals($broken['engine']['cv'], $toBeBroken['engine.cv']);
        $this->assertEquals($broken['engine']['turbo']['pressure'], $toBeBroken['engine.turbo.pressure']);
    }
}