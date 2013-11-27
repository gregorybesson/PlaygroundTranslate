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

        $translator = $serviceManager->get('translator');
       
        // Gestion de la locale
        if (PHP_SAPI !== 'cli') {
            //translator
            $locale = null;
            $options = $serviceManager->get('playgroundcore_module_options');

            if ($e->getRequest()->getCookie() && $e->getRequest()->getCookie()->offsetExists('pg_locale_front')) {
                $locale = $e->getRequest()->getCookie()->offsetGet('pg_locale_front');
            }

            if ($e->getRequest()->getCookie() && $e->getRequest()->getCookie()->offsetExists('pg_locale_back')) {
                $locale = $e->getRequest()->getCookie()->offsetGet('pg_locale_back');
            }


            if (empty($locale)) {
               $locale = $options->getLocale();
            } else {
                $options->setLocale($locale);
            }
            $translator->setLocale($locale);
        
            // plugins
            $translate = $serviceManager->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);

           
        }

        AbstractValidator::setDefaultTranslator($translator,'playgroundtranslate');
        
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
                'playgroundtranslate_translate_service' => 'PlaygroundTranslate\Service\Translate',

            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'switchLocaleWidget' => function ($sm) {
                    $viewHelper = new View\Helper\SwitchLocaleWidget();
                    $viewHelper->setLocaleService($sm->getServiceLocator()->get('playgroundtranslate_locale_service'));
                    
                    return $viewHelper;
                },
            ),
        );
    }
}
