<?php

namespace Zend\EntityMapper\Mapping\Hydration;

/**
 * ArrayBreaker
 *
 * Break a one dimensão
 *
 * @package Zend\EntityMapper\Mapping\Hydration
 */
class ArrayBreaker
{
    /**
     * @param array $toBeBroken
     * @return array
     */
    public static function break(array $toBeBroken): array
    {
        $broken = [];

        foreach ($toBeBroken as $namespace => $data) {
            $decomposedNamespace = explode('.', $namespace);
            $namespaceNodesCount = count($decomposedNamespace);

            if ($namespaceNodesCount === 1) {
                $broken[$namespace] = $data;
            }

            $pointer = &$broken;
            for ($i = 0; $i < $namespaceNodesCount; $i++) {
                if(!isset($pointer[$decomposedNamespace[$i]])) {
                    $pointer[$decomposedNamespace[$i]] = [];
                }

                $pointer = &$pointer[$decomposedNamespace[$i]];

                if($i === $namespaceNodesCount - 1) {
                    $pointer = $data;
                }
            }
        }

        return $broken;
    }
}