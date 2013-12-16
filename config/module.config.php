<?php
return array(
    
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'         => 'phpArray',
                'base_dir'     => __DIR__ . '/../language',
                'pattern'      => '%s.php',
                'text_domain'  => 'playgroundtranslate'
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'playgroundtranslate' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/translate',
                            'defaults' => array(
                                'controller' => 'PlaygroundTranslate\Controller\Admin\TranslateAdmin',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'update' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/update',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundTranslate\Controller\Admin\TranslateAdmin',
                                        'action'     => 'update',
                                    ),
                                ),
                            ),
                        ),
                    ),
                   
                ),
            ),
        ),
    ),

    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'PlaygroundTranslate\Controller\Admin\TranslateAdmin'  => 'PlaygroundTranslate\Controller\Admin\TranslateAdminController',
        ),
    ),
    'navigation' => array(
        'admin' => array(
            'translate'  => array(
                'label' => 'Translate',
                'route' => 'admin/playgroundtranslate',
                'resource' => 'translate',
                'privilege' => 'list',  
            ),
        ),
    ),
);