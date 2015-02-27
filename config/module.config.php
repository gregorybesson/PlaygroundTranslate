<?php
return array(
    'bjyauthorize' => array(
    
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'translate'     => array(),
            ),
        ),
    
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    array(array('admin'), 'translate',      array('list')),
                ),
            ),
        ),
    
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                // Admin area
                array('controller' => 'PlaygroundTranslate\Controller\Admin\TranslateAdmin',    'roles' => array('admin')),
            ),
        ),
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
                        'may_terminate' => true,

                    ),
                   
                ),
            ),
            'apitranslate' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/translate/list/:key[/:locale]',
                    'constraints' => array(
                        'key'       => '[a-zA-Z]+',
                        'locale'     => '[a-zA-Z_]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PlaygroundTranslate\Controller\Api\Translate',
                        'action'     => 'list',
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
            'PlaygroundTranslate\Controller\Api\Translate'  => 'PlaygroundTranslate\Controller\Api\TranslateController',
        ),
    ),
    'navigation' => array(
        'admin' => array(
            'playgroundconfigurationadmin'  => array(
                'pages' => array(
                    'translation' => array(
                        'label' => 'Translations',
                        'route' => 'admin/playgroundtranslate',
                        'resource' => 'translate',
                        'privilege' => 'list', 
                    ),
                ),
            ),
        ),
    ),
    'assetic_configuration' => array(
        'modules' => array(
            'playground_translate' => array(
                # module root path for your css and js files
                'root_path' => array(
                        __DIR__ . '/../view/admin/assets',
                ),
                # collection of assets
                'collections' => array(
                    'admin_translate_css' => array(
                        'assets' => array(
                            'style.css' => 'css/style.css',
                        ),
                        'options' => array(
                            'output' => 'zfcadmin/css/translate.css',
                        )
                    ),
                    'admin_translate_js' => array(
                        'assets' => array(
                            'script.js' => 'js/script.js',
                        ),
                        'options' => array(
                            'output' => 'zfcadmin/js/translate.js',
                        )
                    ),
                ),
            ),
        ),
        'routes' => array(
            'admin.*' => array(
                '@admin_translate_css'     => '@admin_translate_css',
                '@admin_translate_js'     => '@admin_translate_js',
            ),
        ),
    ),
);