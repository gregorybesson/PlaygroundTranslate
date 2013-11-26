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
    /**
    * @var ServiceLocator $serviceLocator
    */
    protected $serviceLocator;
     /**
    * @var Form/Translate $translateForm
    */
    protected $translateForm;
     /**
    * @var Service/Translate $translateService
    */
    protected $translateService;
     /**
    * @var Service/Locale $localeService
    */
    protected $localeService;


    /**
    * indexAction : Permet de recuperer les traductions et de les mettre a jour
    * Upload de fichier CSV
    *
    * @return array $array Passage des variables dans le template
    * form : Formulaire
    * locales : Locales
    * user : user
    * translates : traductions
    * keys : clé des traductions
    */
    public function indexAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $locales = $this->getLocaleService()->getLocaleMapper()->findAll();

        $localesForm = array();
        foreach ($locales as $key => $locale) {
            $localesForm[$locale->getLocale()] = $locale->getName(). " (".$locale->getLocale().")";
        }

        $form = $this->getTranslateForm();
        $form->get('locale')->setValueOptions($localesForm);

        $request = $this->getRequest();
     
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );  

            $return  = $this->getTranslateService()->upload($data);
            
            if(! $return){
                $this->flashMessenger()->addMessage('The translate has not been updated');
            }else{
                $this->flashMessenger()->addMessage('The translate has been updated');
            }
            sleep(2);
            return $this->redirect()->toRoute('admin/playgroundtranslate');
          }

        $viewModel = new ViewModel();

        $translates = $this->getTranslateService()->readLanguagesFiles();
        $keys = array();
        foreach ($translates as $locale => $translate) {
            foreach (array_keys($translate) as $value) {
                if(empty($keys[$value])) {
                    $keys[$value] = $value;
                }
            }
        }
        
        return $viewModel->setVariables(array('form' => $form, 
                                              'locales' => $locales,
                                              'user' => $user,
                                              'translates' => $translates,
                                              'keys' => $keys));
    }

    /**
    * updateAction : Mise a jour à la volee des traduction
    *
    * @return redirect to admin/translate
    */
    public function updateAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
             $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            ); 
            foreach ($data['translate'] as $locale => $values) {
                var_dump($values);
                $return  = $this->getTranslateService()->writeFile($locale, $values);

                if($return === false){
                    $this->flashMessenger()->addMessage('The translate has not been updated');

                    return $this->redirect()->toRoute('admin/playgroundtranslate');
                }

                $return = $this->getTranslateService()->activeTranslate($locale);

                if($return === false){
                    $this->flashMessenger()->addMessage('The translate has not been updated');

                    return $this->redirect()->toRoute('admin/playgroundtranslate');
                }
                $this->flashMessenger()->addMessage('The translate has been updated');
            }
            sleep(2);

            return $this->redirect()->toRoute('admin/playgroundtranslate');
        }

        return $this->redirect()->toRoute('admin/playgroundtranslate');
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
    * getTranslateService : Recuperer le service des traductions
    *
    * @return Service/Translate $translateService
    */
    public function getTranslateService()
    {
        if($this->translateService === null){
            $this->translateService = $this->getServiceLocator()->get('playgroundtranslate_translate_service');
        }
        return $this->translateService;
    }

    /**
    * getTranslateForm : Recuperer le service des traductions
    *
    * @return Form/Translate $translateForm
    */
    public function getTranslateForm()
    {
        if($this->translateForm === null){
            $this->translateForm = $this->getServiceLocator()->get('playgroundtranslate_translate_form');
        }
        return $this->translateForm;
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
}