<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'zf-hipsters' => array(
        'zfh-crud-factory' => array(
            'assetFolder' => 'CrudFactory/src/CrudFactory/Assets',
        ),
    ),
    'router' => array(
        'routes' => array(
            'assets' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/assets/[:type[/:file]]',
                    'constraints' => array(
                        'type'     => '[a-zA-Z0-9_-]*',
                        'file'     => '[a-zA-Z0-9._-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'CrudFactory\Controller',
                        'controller'    => 'Assets',
                        'action'        => 'render',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
