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

        $return = $this->writeFile($data['locale'], $content, 'csv');
        if($return === false){
            return false;
        }

        return $this->activeTranslate($data['locale']);

    }

    public function uploadExcel($data)
    {
        $objPHPExcel = \PHPExcel_IOFactory::load($data['uploadTranslateExcel']['tmp_name']);
        $content = array();
        for ($i=1; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) { 
            $value = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue();
            if($value == "translate empty value") {
                $value = " ";
            }
            $content[$objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue()] = $value;
        }
        $return = $this->writeFile($data['locale'], $content, 'excel');
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
    
        while (($cols = fgetcsv($handle, 0, "\t")) !== FALSE) {
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
    public function writeFile($locale, $content, $context = null)
    {
        $translate = "";
        foreach ($content as $key => $value) {
            $translate .= '    "'.$key.'" => "'.str_replace('"', '\"', $value).'",'."\n"; 
        }
        
        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        $template = file_get_contents(__DIR__.'/../Templates/translate.php');
        $contentTranslate = str_replace("{{translate}}", $translate, $template);

        return file_put_contents(__DIR__.$pathTranslate.$locale.'.php.tmp', $contentTranslate);
    }

    /**
    * activeTranslate : Renommage du fichier temporaire pour l'activer sous ZF
    * @param string $locale locale
    * 
    * @return boolean $return retour du rename
    */
    public function activeTranslate($locale)
    {
        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        $oldFile = __DIR__.$pathTranslate.$locale.'.php';
        if(file_exists($oldFile)){
            rename($oldFile, __DIR__.$pathTranslate.'/revisions/'.$locale.'.php.'.date("YmdHis"));
        }

        rename(__DIR__.$pathTranslate.$locale.'.php.tmp', $oldFile);
        
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

        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        $dir = opendir(__DIR__.$pathTranslate);
        while($file = readdir($dir)) { 
            if($file != '.' && $file != '..' && !is_dir(__DIR__.$pathTranslate.$file)) {
                $translates[basename(__DIR__.$pathTranslate.$file, '.php')] = @include __DIR__.$pathTranslate.$file;
            }
        }


        return $translates;
    }

    /**
    * readLanguageFile : Permet de lire un fichier de traductions en fonction de la locale
    * @param string $locale : locale 
    *
    * @return array $translates tableau de traductions
    */
    public function readLanguageFile($locale)
    {
        $translates = array();

        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        return @include(__DIR__.$pathTranslate.$locale.".php");
    }


    public function export($data)
    {

        $translates = array();

        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        $translates = @include __DIR__.$pathTranslate.$data['locale'].'.php';
        return $translates;
    }

    /**
    * convertArrayToCSV : convertit un tableau en CSV
    * @param array $translates tableau de traduction
    * 
    * @return string $content contenu des traduction au format csv
    */
    public function convertArrayToCSV($translates)
    {
        $content = '';

        foreach ($translates as $key => $value) {
            $content .= $key.';'.$value."\n";
        }
        
        return $content;
    }

    /**
    * getHistory : Permet de recuperer les historiques de traductions
    * @param array $historicals tableau de d'historique de traductions
    * @param string $key clé de locale 
    * 
    * @return array $historicals tableau de d'historique de traductions
    */
    public function getHistory($historicals, $key)
    {
        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();
        $dir = opendir(__DIR__.$pathTranslate.'revisions/');
        while($file = readdir($dir)) { 
            if($file != '.' && $file != '..' && !is_dir(__DIR__.$pathTranslate.$file) && strpos($file, $key) !== false) {
                $filename = explode(".", $file);
                $datetime = $filename[2];
                $datetime = \DateTime::createFromFormat('YmdHis', $datetime);
                $historicals[$filename[2]] = array('locale' => $key,
                                                'datetime'=> $datetime);
            }
        }

        return $historicals;
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