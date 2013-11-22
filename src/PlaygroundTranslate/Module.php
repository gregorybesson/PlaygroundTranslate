<?php

namespace PlaygroundTranslate;

use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    protected $eventsArray = array();
    
    public function onBootstrap(MvcEvent $e)
    {
        $application     = $e->getTarget();
        $serviceManager  = $application->getServiceManager();
        $eventManager    = $application->getEventManager();

        $options = $serviceManager->get('playgroundcore_module_options');
        $locale = $options->getLocale();
        $translator = $serviceManager->get('translator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $serviceManager->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }
        AbstractValidator::setDefaultTranslator($translator,'playgroundtranslate');

        $options = $serviceManager->get('playgroundtranslate_module_options');
        $config = $serviceManager->get('Config');
        $options->setLocales($config['locales']);
        
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'playgroundtranslate_doctrine_em' => 'doctrine.entitymanager.orm_default',
            ),
            'factories' => array(
                'playgroundtranslate_module_options' => function  ($sm) {
                    $config = $sm->get('Configuration');
                    return new Options\ModuleOptions(isset($config['playgroundtranslate']) ? $config['playgroundtranslate'] : array());
                },
                'playgroundtranslate_translate_form' => function  ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Translate(null, $sm, $translator);
                    
                    return $form;
                },
                'playgroundtranslate_locale_mapper' => function  ($sm) {
                    return new Mapper\Locale($sm->get('playgroundtranslate_doctrine_em'), $sm->get('playgroundtranslate_module_options'));
                },
            ),
            'invokables' => array(
                'playgroundtranslate_locale_service' => 'PlaygroundTranslate\Service\Locale',
                'playgroundtranslate_translate' => 'PlaygroundTranslate\Service\Translate',

            ),
        );
    }
}
