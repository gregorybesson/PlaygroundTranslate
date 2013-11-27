<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PlaygroundTranslate\Controller\Frontend;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SwitchLocaleController extends AbstractActionController implements ServiceLocatorAwareInterface
{
    /**
    * @var $localeService : Service des locales
    */
    protected $localeService;

    /**
    * switchAction : permet de switcher de langue en fonction d'un context (back/front)
    * locale : locale pour switch
    * context : (back/front)
    * referer : retour à la page
    *
    * @return Redirect $redirect redirect to referer
    */
    public function switchAction()
    {
        $locale = $this->getEvent()->getRouteMatch()->getParam('locale');
        $context = $this->getEvent()->getRouteMatch()->getParam('context');
        $referer = urldecode($this->getEvent()->getRouteMatch()->getParam('referer'));
        
        if ($context == 'front'){
            $referer = str_replace("%channel%", $this->getEvent()->getRouteMatch()->getParam('channel'), $referer);
            $referer = str_replace("//", '/', $referer);
        }
        
        $filter = 'active_'.$context;
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array($filter => 1, 'locale' => $locale));
                
        // Si pas de locale, on redirige sans rien faire
        if(count($locales) != 1){
            
            return $this->redirect()->toUrl($referer);
        }

        $locale = $locales[0];
        $cookie = new \Zend\Http\Header\SetCookie('pg_locale_'.$context, $locale->getLocale(), time() + 60*60*24*365,'/');
        $this->getResponse()->getHeaders()->addHeader($cookie);
        
        return $this->redirect()->toUrl($referer);
    }


    /**
    * getServiceLocator : Recuperer le service locator
    * @return ServiceLocator $serviceLocator
    */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
    * setServiceLocator : set le service locator
    */
    public function setServiceLocator (ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
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
}