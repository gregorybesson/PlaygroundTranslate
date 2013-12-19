<?php


namespace PlaygroundTranslate\Controller\Api;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\AbstractActionController;

use Zend\Http\Response;

class TranslateController extends AbstractActionController implements ServiceLocatorAwareInterface
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
    * Permet de récuperer la liste des traductions
    *  
    * key : clé de traduction à chercher
    * locale : locale de traduction
    *
    * Retour un tableau JSON des traductions
    *
    * @return Reponse $response
    */
    public function listAction()
    {
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $headers = $response->getHeaders(); 
        $headers->addHeaderLine('Cache-Control', 'public, max-age=86400'); 
        $headers->addHeaderLine('Expires', date(DATE_RFC822, strtotime("1 day"))); 


        $key = strtolower($this->getEvent()->getRouteMatch()->getParam('key'));
        $locale = $this->getEvent()->getRouteMatch()->getParam('locale');

        if (strlen($key)<=2) {
            $response->setContent(json_encode(array('status' => 1, 'message' => "The key length should be greater than 3!")));
        
            return $response;
        }

        $translatesSelected = array();
        if (empty($locale)) {
            $locale = 'en_UK';
        }
        $translatesSelected = array();
        $translates = $this->getTranslateService()->readLanguageFile($locale);
        if ($translates) {
            foreach ($translates as $translateKey => $translateValue) {
                $pattern = "/^".$key."/";
                if (preg_match($pattern, strtolower($translateKey))) {
                    $translatesSelected[$translateKey] = $translateValue;
                }
            }
        }

        $return['status'] = 0;
        $return['translates'] = $translatesSelected;

        $response->setContent(json_encode($return));
        
        return $response;
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