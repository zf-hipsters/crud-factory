<?php
namespace CrudFactory\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Stdlib\Hydrator\ArraySerializable;

class ArrayEntity implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('CrudFactory\Service\Factory\Config');
        $tableConfig = $config['table_configuration'];

        $properties = array();
        foreach ($tableConfig as $key=>$value) {
            $properties[$key] = '';
        }

        $entity = clone $serviceLocator->get('CrudFactory\Entity\ArrayEntity');
        $hydrate = new ArraySerializable();
        $hydrate->hydrate($properties, $entity);

        return $entity;
    }
}