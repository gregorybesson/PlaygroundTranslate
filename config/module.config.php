<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'doctrine' => array(
        'driver' => array(
            'playgroundtranslate_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundTranslate/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundTranslate\Entity'  => 'playgroundtranslate_entity'
                )
            )
        )
    ),
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
            'PlaygroundTranslate\Controller\Admin\TranslateAdmin' => 'PlaygroundTranslate\Controller\Admin\TranslateAdminController',
        ),
    ),
    'navigation' => array(
        'admin' => array(
            'translate'  => array(
                'label' => 'Translate',
                'route' => 'admin/playgroundtranslate',
                'resource' => 'translate',
                'privilege' => 'list',
                'pages' => array(
                    'list-pages' => array(
                        'label' => 'Translate',
                        'route' => 'admin/playgroundtranslate',
                        'resource' => 'translate',
                        'privilege' => 'list',
                    ),
                ),    
            ),
        ),
    ),
    'locales' => array(
        'fr_FR' => 'Français',
        'en_US' => 'Anglais'
    )
);