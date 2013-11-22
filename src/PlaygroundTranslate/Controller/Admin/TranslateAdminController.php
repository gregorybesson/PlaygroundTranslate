<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PlaygroundTranslate\Controller\Admin;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TranslateAdminController extends AbstractActionController implements ServiceLocatorAwareInterface
{
    public function indexAction()
    {

        $sl = $this->getServiceLocator(); 

        $user = $this->zfcUserAuthentication()->getIdentity();
        $locales = array();
        if($user->getRole()->getRoleId()=="admin") {    
            foreach ($user->getSiteCountries() as $siteCountry) {
                foreach ($siteCountry->getLocales() as $localesByCountry) {
                    $locales[] = $localesByCountry;
                }
            }
        }
        elseif ($user->getRole()->getRoleId()=="super_admin") {
            $locales = $sl->get('playgroundtranslate_locale_service')->getLocaleMapper()->findAll();
        }

        $localesForm = array();
        foreach ($locales as $key => $locale) {
            $localesForm[$locale->getLocale()] = $locale->getName(). " (".$locale->getLocale().")";
        }

        $form = $sl->get('playgroundtranslate_translate_form');
        $form->get('locale')->setValueOptions($localesForm);

        $request = $this->getRequest();

     
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );  

            $return  = $sl->get('playgroundtranslate_translate_service')->upload($data);
            
            if(! $return){
                $this->flashMessenger()->addMessage('The translate has not been updated');
            }else{
                $this->flashMessenger()->addMessage('The translate has been updated');
            }
            sleep(2);
            return $this->redirect()->toRoute('admin/playgroundtranslate');
          }

        $viewModel = new ViewModel();

        return $viewModel->setVariables(array('form' => $form, 'locales', $locales));
    }


    public function getTranslateOptions()
    {
        return $this->getServiceLocator()->get('playgroundtranslate_module_options');
    }

    protected $serviceLocator;

    public function getServiceLocator ()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator (ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}