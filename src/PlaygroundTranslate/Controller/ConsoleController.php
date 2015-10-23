<?php

namespace PlaygroundTranslate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Response as ConsoleResponse;
use PlaygroundTranslate\Service\Translate;

class ConsoleController extends AbstractActionController
{

    /**
     * @var translateService
     */
    protected $translateService;

    public function createTranslationFilesAction()
    {

        $request = $this->getRequest();
        $response = $this->getResponse();

        $folder = $request->getParam('path');

        /*if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }*/

        $ts = $this->getTranslateService();

        $result = $ts->createTranslationFiles($folder);

        if (!$response instanceof ConsoleResponse) {
            $response->setStatusCode(200);
            $response->setContent('ok');

            return $response;
        } else {
            return $result;
        }
    }

    public function getTranslateService()
    {
        if (!$this->translateService) {
            $this->translateService = $this->getServiceLocator()->get('playgroundtranslate_translate_service');
        }

        return $this->translateService;
    }

    public function setTranslateService(Translate $translateService)
    {
        $this->translateService = $translateService;

        return $this;
    }
}
