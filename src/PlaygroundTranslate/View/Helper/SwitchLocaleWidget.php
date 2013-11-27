<?php

namespace PlaygroundTranslate\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SwitchLocaleWidget extends AbstractHelper
{
    
    protected $localeService;
    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {
        $locales = array();
        if(empty($options['context'])){
            $locales['locales'] = $this->getLocaleService()->getLocaleMapper()->findAll();
            $locales['context'] = null;

            return $locales;
        }
        $filter = 'active_'.$options['context'];
        $locales['locales'] = $this->getLocaleService()->getLocaleMapper()->findBy(array($filter => 1));
        $locales['context'] = $options['context'];

        return $locales;
    }

     /**
    * getLocaleService : Recuperer le service des locales
    *
    * @return Service/Locale $localeService
    */
    public function getLocaleService()
    {
        if($this->localeService === null){
            $this->localeService = $this->getServiceLocator()->get('playgroundtranslate_locale_service');
        }
        return $this->localeService;
    }

    /**
    * setLocaleService : set le service locale
    */
    public function setLocaleService ($localeService)
    {
        $this->localeService = $localeService;
    }
 

}