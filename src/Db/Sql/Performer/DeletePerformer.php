<?php

namespace Zend\EntityMapper\Db\Sql\Performer;

use Zend\Db\TableGateway\TableGateway;
use Zend\EntityMapper\Config\Container\Container;
use Zend\EntityMapper\Db\Sql\Performer\Base\AbstractPerformer;
use Zend\EntityMapper\Mapping\Extraction\Extractor;

/**
 * DeletePerformer
 *
 * @package Zend\EntityMapper\Db\Sql\Performer
 */
class DeletePerformer extends AbstractPerformer
{
    /**
     * @var Container
     */
    private $container;

    /**
     * DeletePerformer constructor.
     *
     * @param TableGateway $tableGateway
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->container = new Container();
    }

    /**
     * @param $object
     * @throws \Zend\Cache\Exception\ExceptionInterface
     * @throws \Zend\EntityMapper\Config\Container\Exceptions\ItemNotFoundException
     */
    public function perform($object)
    {
        $entityName = get_class($object);
        $entityConf = $this->container->get($entityName);
        $primaryKey = null;
        $reflection = new \ReflectionObject($object);

        foreach ($entityConf->getFields() as $field) {
            if ($field->isPrimaryKey()) {
                $primaryKey = $field;
            }
        }

        $primaryKeyValue = $reflection->getProperty($primaryKey->getProperty())->getValue($object);
        $primaryKeyWhere = [$primaryKey->getAlias() => $primaryKeyValue];

        $this->tableGateway->delete($primaryKeyWhere);
    }
}