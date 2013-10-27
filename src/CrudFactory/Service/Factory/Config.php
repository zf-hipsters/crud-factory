<?php
namespace CrudFactory\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Config implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Zend\Mvc\Router\Http\TreeRouteStack $router */
        $router=$serviceLocator->get('Router');
        $request=$serviceLocator->get('Request');
        $routeMatch=$router->match($request);

        $config = $serviceLocator->get('config');

        return $config['crud-factory'][strtolower($routeMatch->getParam('controller'))];

    }
}