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
    /*
     * @var PATH_LANGUAGE : path qui definit les fichiers de traductions
     */
    const PATH_LANGUAGE = '/../../../language/';
    /*
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
    * upload : permet d'upload un CSV et de le retranscrire en un fichier de traduction sous ZF
    * @param array $data file upload form
    * 
    * @return boolean $return status de la fonction
    */
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

    /**
    * readCSV : Lire un fichier CSV et le formater dans un tableau php
    * @param array $data file upload form
    * 
    * @return array $content array de traductions
    */
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

    /**
    * writeFile : Ecrire un fichier temporaire php de traduction 
    * @param string $locale locale
    * @param array $content array de traductions
    * 
    * @return mixed $mixed retour du file_put_contents pour le fichier temporaire
    */
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

    /**
    * activeTranslate : Renommage du fichier temporaire pour l'activer sous ZF
    * @param string $locale locale
    * 
    * @return boolean $return retour du rename
    */
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
    * readLanguagesFiles : Permet de lire les fichiers de traductions
    * 
    * @return array $translates tableau de traductions
    */
    public function readLanguagesFiles()
    {
        $translates = array();

        $dir = opendir(__DIR__.self::PATH_LANGUAGE);
        while($file = readdir($dir)) { 
            if($file != '.' && $file != '..' && !is_dir(__DIR__.self::PATH_LANGUAGE.$file)) {
                $translates[basename(__DIR__.self::PATH_LANGUAGE.$file, '.php')] = @include __DIR__.self::PATH_LANGUAGE.$file;
            }
        }


        return $translates;
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