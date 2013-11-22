<?php

namespace PlaygroundTranslate\Service;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Validator\NotEmpty;
use ZfcBase\EventManager\EventProvider;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Stdlib\ErrorHandler;
use PlaygroundTranslate\Lib\Excel\ExcelReader;

class Translate extends EventProvider implements ServiceManagerAwareInterface
{

    const PATH_LANGUAGE = '/../../../language/';
   /*
     * @var ServiceManager
     */
    protected $serviceManager;


    public function upload(array $data)
    {
        $content = $this->readCSV($data);
        if($content === false){
            return false;
        }

        $return = $this->writeFile($data['locale'], $content);
        if($return === false){
            return false;
        }

        return $this->activeTranslate($data['locale']);

    }


    public function readCSV($data) 
    {
        $content = array();
        if ( !file_exists($data['uploadTranslate']['tmp_name'])) {
            return false;
        }
        if (($handle = fopen($data['uploadTranslate']['tmp_name'], "r")) === FALSE){
            return false;
        }

        while (($cols = fgetcsv($handle, 1000, "\t")) !== FALSE) {
            foreach ($cols as $row) {
                $rowTab = explode(";", $row);
                if(!empty($rowTab[0]) && !empty($rowTab[1])) {
                    $content[$rowTab[0]] = $rowTab[1];  
                }
            }
        }

        return $content;
    }

    public function writeFile($locale, $content)
    {
       $translate = "";
       foreach ($content as $key => $value) {
           $translate .= "    '".$key."' => '".$value."',\n";
       }

        $template = file_get_contents(__DIR__.'/../Templates/translate.php');
        $contentTranslate = str_replace("{{translate}}", $translate, $template);

        return file_put_contents(__DIR__.self::PATH_LANGUAGE.$locale.'.php.tmp', $contentTranslate);
    }

    public function activeTranslate($locale)
    {
        $oldFile = __DIR__.self::PATH_LANGUAGE.$locale.'.php';
        if(file_exists($oldFile)){
            rename($oldFile, __DIR__.self::PATH_LANGUAGE.'/revisions/'.$locale.'.php.'.date("YmdHis"));
        }

        rename(__DIR__.self::PATH_LANGUAGE.$locale.'.php.tmp', $oldFile);
        
        return true;
    }


    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}