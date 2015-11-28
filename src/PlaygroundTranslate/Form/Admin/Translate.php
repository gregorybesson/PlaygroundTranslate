<?php

namespace PlaygroundTranslate\Form\Admin;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class Translate extends ProvidesEventsForm
{
    /**
    * @var Zend\ServiceManager\ServiceManager $serviceManager
    */
    protected $serviceManager;

    /**
    * __construct : permet de construire le formulaire qui peuplera l'entity theme
    *
    * @param string $name
    * @param Zend\ServiceManager\ServiceManager $serviceManager
    * @param Zend\I18n\Translator\Translator $translator
    *
    */
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        $this->serviceManager = $serviceManager;
        parent::__construct($name);

        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'uploadTranslate',
            'attributes' => array(
                'type'  => 'file',
            ),
            'options' => array(
                'label' => $translator->translate('import translate', 'playgroundtranslate'),
            ),
        ));

         $this->add(array(
            'name' => 'uploadTranslateExcel',
            'attributes' => array(
                'type'  => 'file',
            ),
            'options' => array(
                'label' => $translator->translate('import translate', 'playgroundtranslate'),
            ),
         ));


         $this->add(array(
            'name' => 'translate',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                    'value' => '',
            ),
         ));

         $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'locale',
            'options' => array(
                'label' => $translator->translate('locale', 'playgroundtranslate'),
                'value_options' => $this->getLocales(),
            )
         ));



         $submitElement = new Element\Button('submit');
         $submitElement->setAttributes(array('type'  => 'submit'));

         $this->add($submitElement, array('priority' => -100));
    }

    /**
    * getLocales :permet de recuperer toutes les locales
    *
    * @return array $localesForm
    *
    */
    public function getLocales()
    {
        $locales = $this->serviceManager->get('playgroundcore_locale_service')->getLocaleMapper()->findAll();
        $localesForm = array();
        foreach ($locales as $key => $locale) {
            $localesForm[$locale->getLocale()] = $locale->getName(). " (".$locale->getLocale().")";
        }
        return $localesForm;
    }
}
