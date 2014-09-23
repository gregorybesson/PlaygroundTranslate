<?php


namespace PlaygroundTranslate\Controller\Admin;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;

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

        $this->getTranslateService()->buildTree();


        $user = $this->zfcUserAuthentication()->getIdentity();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        $localesForm = array();
        foreach ($locales as $key => $locale) {
            $localesForm[$locale->getLocale()] = $locale->getName();
        }


        $form = $this->getTranslateForm();
        $form->get('locale')->setValueOptions($localesForm);

        $request = $this->getRequest();
     
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );  

            if(!empty($data['export'])){
                $return  = $this->getTranslateService()->export($data);
                return $this->exportTranslate($return, $data);
            }else{
                if(!empty($data['uploadTranslateExcel']) && $data['uploadTranslateExcel']['name'] != ''){
                    $return  = $this->getTranslateService()->uploadExcel($data);   
                }
                if(!empty($data['uploadTranslate']) && $data['uploadTranslate']['name'] != ''){
                    $return  = $this->getTranslateService()->upload($data);     
                }
                if(!empty($data['exportexcel'])){
                    $return  = $this->getTranslateService()->export($data);    
                    return $this->exportTranslateExcel($return, $data); 
                }
            }

            
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
        
        $arborescence = $this->getTranslateService()->getArborescence();

        // Gestion de la branche
        if($this->getRequest()->getQuery('controller')) {
            $branch = $arborescence[
                    $this->getRequest()->getQuery('controller', key($arborescence))
                ][
                    $this->getRequest()->getQuery('action', key(current($arborescence)))
                ];
        }
        else {
            $branch = false;

            // On recupere toutes les clées du parsing
            $allKeys = array();
            foreach ($arborescence as $controller => $actions) {
                foreach ($actions as $action => $datas) {
                    $allKeys = array_merge($allKeys, $datas['keys']);
                }
            }
            $allKeysInKey = array();
            foreach ($allKeys as $key) {
                $allKeysInKey[$key] = "";
            }
            // On ajoute les clées de traductions vides trouvées par le parsing
            foreach ($locales as $key => $locale) {
                $translates[$locale->getLocale()] = array_merge($allKeysInKey, $translates[$locale->getLocale()]);
            }
        }

        $historicals = $this->getHistoryTranslate($localesForm);
        krsort($historicals);

        $locale = $this->getRequest()->getQuery('locale', key($localesForm));

        $keys = array();
        foreach ($translates as $translate) {
            if(is_array($translate)){
                foreach (array_keys($translate) as $value) {
                    if(empty($keys[$value])) {
                        $keys[$value] = $value;
                    }
                }
            }
        }

        return $viewModel->setVariables(array('form' => $form, 
                                              'locales' => $locales,
                                              'user' => $user,
                                              'translates' => $translates,
                                              'keys' => $keys,
                                              'localesForm' => $localesForm,
                                              'historicals' => $historicals,
                                              'arborescence' => $arborescence,
                                              'branch' => $branch,
                                              'locale' => $locale));
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
            if($this->getRequest()->getQuery('controller')) {
                $redirect = '?controller=' . $this->getRequest()->getQuery('controller')
                    . '&action=' . $this->getRequest()->getQuery('action')
                    . '&locale=' . $this->getRequest()->getQuery('locale');
            } else {
                $redirect = '';
            }

             $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            ); 
            foreach ($data['translate'] as $locale => $values) {
                $return  = $this->getTranslateService()->writeFile($locale, $values);

                if($return === false){
                    $this->flashMessenger()->addMessage('The translate has not been updated');

                    return $this->redirect()->toUrl($this->url()->fromRoute('admin/playgroundtranslate') . $redirect);
                }

                $return = $this->getTranslateService()->activeTranslate($locale);

                if($return === false){
                    $this->flashMessenger()->addMessage('The translate has not been updated');

                    return $this->redirect()->toUrl($this->url()->fromRoute('admin/playgroundtranslate') . $redirect);
                }
                $this->flashMessenger()->addMessage('The translate has been updated');
            }
            sleep(2);

            return $this->redirect()->toUrl($this->url()->fromRoute('admin/playgroundtranslate') . $redirect);
        }

        return $this->redirect()->toUrl($this->url()->fromRoute('admin/playgroundtranslate') . $redirect);
    }

    /**
    * getHistoryTranslate : Permet de recuperer les historiques de traductions
    * @param  array $localesForm tableau des locales
    *
    * @return array $historicals tableau de d'historique de traductions
    */
    public function getHistoryTranslate($localesForm)
    {
        $historicals = array();
        foreach (array_keys($localesForm) as $key) {
            $historicals = $this->getTranslateService()->getHistory($historicals, $key);
        }

        return $historicals;
    }

    public function exportTranslate($translates, $data)
    {
        $content = '';
        $content = $this->getTranslateService()->convertArrayToCSV($translates);

        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_200);

        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'text/csv')
            ->addHeaderLine('Content-Disposition', "attachment; filename=".$data['locale'] . "-export.csv")
            ->addHeaderLine('Accept-Ranges', 'bytes')
            ->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);
        return $response;
    }

    public function exportTranslateExcel($translates, $data)
    {
        $content = "";
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_200);

        $arborescence = $this->getTranslateService()->getArborescence();
        $keys = array();
        foreach ($arborescence as $controller => $branch) {
            foreach ($branch as $action => $datas) {
                foreach ($datas['keys'] as $key) {
                    $keys[$key] = $controllerName = end(explode('\\', $controller)).'-'.$action;
                }
            }
        }

        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'application/vnd.ms-excel')
            ->addHeaderLine('Content-Disposition', "attachment; filename=".$data['locale'] . "-export.xls")
            ->addHeaderLine('Accept-Ranges', 'bytes')
            ->addHeaderLine('Cache-Control', 'max-age=0')
            ->addHeaderLine('Accept-Ranges', 'max-age=1')
            ->addHeaderLine('Cache-Control', 'cache, must-revalidate');

        $content = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr"> 
            <head> 
            <title>Planification Connect</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            </head>
            <body><table>';

            foreach ($translates as $key => $value) {
                $content .="<tr><td>".htmlspecialchars($key)."</td><td>".htmlspecialchars($value)."</td><td>".htmlspecialchars($keys[$key])."</td></tr>";
            }

        $content.="</table></body>";
        $response->setContent($content);
        return $response;
    }

    /**
    * getLocaleService : Recuperer le service des locales
    *
    * @return Service/Locale $localeService
    */
    public function getLocaleService()
    {
        if($this->localeService === null){
            $this->localeService = $this->getServiceLocator()->get('playgroundcore_locale_service');
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