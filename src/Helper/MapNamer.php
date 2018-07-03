<?php

namespace Zend\EntityMapper\Helper;

class MapNamer
{
    public static function getNameFor($class) {
        return $class . 'Config';
    }
}