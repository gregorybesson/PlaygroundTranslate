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
            $locale = null;
            $options = $serviceManager->get('playgroundcore_module_options');
            
            $locale = $options->getLocale();

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

                

               
            ),
            'invokables' => array(
                'playgroundtranslate_translate_service' => 'PlaygroundTranslate\Service\Translate',
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'translate' => function ($sm) {
                    $viewHelper = new View\Helper\Translate();/*
                    $viewHelper->setOptions($sm->getServiceLocator()->get('playgroundtranslate_module_options'));
                    $viewHelper->setRequest($sm->getServiceLocator()->get('request'));
                    $viewHelper->setMvcEvent($sm->getServiceLocator()->get('application')->getMvcEvent());*/
                    return $viewHelper;
                },
            )
        );

    }
}
