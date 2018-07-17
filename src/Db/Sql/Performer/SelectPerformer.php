<?php

namespace Zend\EntityMapper\Db\Sql\Performer;

use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\EntityMapper\Db\Sql\Performer\Base\AbstractPerformer;
use Zend\EntityMapper\Mapping\Hydration\ArrayBreaker;
use Zend\EntityMapper\Mapping\Hydration\Hydrator;

/**
 * Class SelectPerformer
 *
 * @package Zend\EntityMapper\Db\Sql\Performer
 */
class SelectPerformer extends AbstractPerformer
{
    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var string
     */
    private $entity;

    /**
     * SelectPerformer constructor.
     *
     * @param TableGatewayInterface $tableGateway
     */
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->hydrator = new Hydrator();
    }

    /**
     * @param Hydrator $hydrator
     */
    public function setHydrator(Hydrator $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @param $entity
     */
    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @param $select
     * @return array
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function perform($select): array
    {
        $rows = $this->tableGateway->selectWith($select)->toArray();
        $rowsToBeShown = [];

        foreach ($rows as $row) {
            $brokenRow = ArrayBreaker::break($row);
            $subject = new $this->entity;
            $rowsToBeShown[] = $this->hydrator->hydrate($brokenRow, $subject);
        }

        return $rowsToBeShown;
    }
}
