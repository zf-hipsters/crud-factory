<?php
namespace CrudFactory\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TableGateway implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Router\Http\TreeRouteStack $router */
        $router = $serviceLocator->get('Router');
        $request = $serviceLocator->get('Request');
        $routeMatch = $router->match($request);

        $config = $serviceLocator->get('config');

        $moduleConfig = $config['crud-factory'][strtolower($routeMatch->getParam('controller'))];

        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');

        $hydrator = $serviceLocator->get($moduleConfig['hydrator_class']);
        $rowObjectPrototype = $serviceLocator->get($moduleConfig['entity_prototype']);

        $resultSet = new \Zend\Db\ResultSet\HydratingResultSet($hydrator, $rowObjectPrototype);

        return $tableGateway = new \Zend\Db\TableGateway\TableGateway($moduleConfig['table'], $dbAdapter, null, $resultSet);

    }
}