<?php
namespace CrudFactory\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;

class DbAdapter implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        return new Adapter(array(
            'driver'    => 'pdo',
            'dsn'       => 'mysql:dbname='.$config['database'].';host='.$config['hostname'],
            'database'  => $config['database'],
            'username'  => $config['username'],
            'password'  => $config['password'],
            'hostname'  => $config['hostname'],
        ));

    }
}