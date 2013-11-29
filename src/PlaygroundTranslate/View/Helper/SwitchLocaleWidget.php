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
        $activeFilter = 'active_'.$options['context'];
        $filters = array($activeFilter => 1);
        if($options['context'] == 'front') {
            $locale = str_replace("/", "", $this->getRouteMatch()->getParam('locale'));
            $websites = $this->getWebsiteService()->getWebsiteMapper()->findBy(array('code' => $locale));
            if(empty($websites)){
               return array('locales' => array());
            } 
            $website = $websites[0];
            $locales['locales'] = $website->getLocales();
            if(count($locales['locales']) == 1) {
                return array('locales' => array());
            }
        } else {
           $locales['locales'] = $this->getLocaleService()->getLocaleMapper()->findBy($filters); 
        }
        
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
    
    public function setRouteMatch($routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

     public function getRouteMatch()
    {
      
        return $this->routeMatch;
    }

    public function getWebsiteService()
    {
        if (null === $this->websiteService) {
            $this->websiteService = $this->getServiceLocator()->get('playgroundtranslate_website_service');
        }

        return $this->websiteService;
    }

    public function setWebsiteService($websiteService)
    {
        $this->websiteService = $websiteService;

        return $this;
    }
}