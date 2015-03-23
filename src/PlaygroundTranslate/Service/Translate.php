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

    public static $EMPTY_VALUE = "itsanemptytranslation";

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
            $content[$objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue()] = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue();
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
        // On recupère toutes les traductions
        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();
        $data = include(__DIR__.$pathTranslate.$locale.'.php');
        
        // On ajoute les nouvelles
        // $content = array_merge($data, $content); will not work if numeric key exists !
        // beware of the order below (to overwrite)
        $content += $data;

        $translate = "";
        foreach ($content as $key => $value) {
            if ( $value == self::$EMPTY_VALUE) {
                $value = " ";
            }
            $translate .= '    "'.str_replace('"', '\"',stripslashes($key)).'" => "'.str_replace('"', '\"', $value).'",'."\n"; 
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

    public function getArborescence() {
        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();
        $path = __DIR__.$pathTranslate.'categories/';

        $arbo = array();
        foreach (glob($path.'*') as $folder) {
            if(is_dir($folder)) {
                foreach (glob($folder.'/*') as $file) {
                    $content = json_decode(file_get_contents($file), true);
                    $arbo[$content['controller']][$content['action']] = $content;
                }
            }
        }
        return $arbo;
    }

    public function parseTemplate($template)
    {
        // on recupere toutes les informations dans le ->translate()
        $raw = file_get_contents($template);
        $tabs = preg_split('#\$this->translate\(#i', $raw);
        $keys = array();
        foreach ($tabs as $key => $value) {
            $split = str_split($value);
            $cpt = 1;
            $key = '';
            foreach ($split as $car) {
                if($car == '(') {
                    $cpt ++;
                }
                elseif($car == ')') {
                    $cpt --;
                }

                if($cpt==0) {
                    if($key[0] == '\'' || $key[0] == '\"' ) { // teste si c'est une chaine
                        $keys[] = $key;
                    }
                    break;
                }
                else {
                    $key .= $car;
                }
            }
        }

        // traitement pour recupere juste la clé
        $res = array();
        foreach ($keys as $value) {
            $split = str_split($value);
            $in = false;
            $key = '';
            $cpt = 0;
            $firstOpenTag = false;
            foreach ($split as $car) {
                if(!$firstOpenTag) {
                    $firstOpenTag = $car;
                }
                if(($car == $firstOpenTag) && !$in && (!array_key_exists($cpt - 1, $split) || (array_key_exists($cpt - 1, $split) && $split[$cpt - 1] != "\\"))) {
                    $in = true;
                }
                elseif(($car == $firstOpenTag) && $in && (!array_key_exists($cpt - 1, $split) || (array_key_exists($cpt - 1, $split) && $split[$cpt - 1] != "\\"))) {
                    $in = false;
                }

                if($in) {
                    $key .= $car;
                }
                else {
                    $word = ltrim(ltrim($key, '\''), '"');
                    $res[] = str_replace(array('\\\'', '\\"'), array('\'', '"'), $word);
                    break;
                }
                $cpt++;
            }
        }

        return $res;
    }

    public function parseForPartials($template)
    {
        // on recupere toutes les informations dans le ->translate()
        $raw = file_get_contents($template);
        $tabs = preg_split('#\$this->partial\(#i', $raw);
        $keys = array();
        foreach ($tabs as $key => $value) {
            $split = str_split($value);
            $cpt = 1;
            $key = '';
            foreach ($split as $car) {
                if($car == '(') {
                    $cpt ++;
                }
                elseif($car == ')') {
                    $cpt --;
                }

                if($cpt==0) {
                    if($key[0] == '\'' || $key[0] == '\"' ) { // teste si c'est une chaine
                        $keys[] = /*trim(trim(*/$key/*, '\''), '\"')*/;
                    }
                    break;
                }
                else {
                    $key .= $car;
                }
            }
        }

        // traitement pour recupere juste la clé
        $res = array();
        foreach ($keys as $value) {
            $split = str_split($value);
            $in = false;
            $key = '';
            foreach ($split as $car) {
                if(($car == '\'' || $car == '\"') && !$in) {
                    $in = true;
                }
                elseif(($car == '\'' || $car == '\"') && $in) {
                    $in = false;
                }

                if($in) {
                    $key .= $car;
                }
                else {
                    $res[] = ltrim(ltrim($key, '\''), '\"');
                    break;
                }
            }
        }

        return $res;
    }

    public function buildTree()
    {
        // layout
        $config = $this->getServiceManager()->get('Config');
        $this->parseLayout($config['core_layout']['frontend']['layout']);

        // templates
        $routes = $config['router']['routes'];
        foreach ($routes as $key => $route) {
            $this->buildTreeRecursive(1, $key, $route, "");
        }
    }

    public function parseLayout($layout) {
        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        $config = $this->getServiceManager()->get('Config');
        $design = implode('/', $config['design']['frontend']);

        $template = __DIR__.'/../../../../../../design/frontend/' . $design .'/'.$layout;

        $keys = $this->parseTemplate($template);
        $path = __DIR__.$pathTranslate.'categories/';
        if(!file_exists($path)) {
            mkdir($path, true);
            chmod($path, 0777);
        }
        $data = array(
            'keys' => array_unique($keys), // On enleve les doublons
            'layout' => $layout,
            'template' => $template,
        );
        file_put_contents($path.'/layout.json', json_encode($data));
    }

    public function buildTreeRecursive($row, $key, $route, $url)
    {
        
        switch ($route['type']) {
            case 'Literal':
            case 'segment':
            case 'Segment':
            case 'Zend\Mvc\Router\Http\Literal':
                $uri = $route['options']['route'];
                
                break;

            case 'PlaygroundCore\Mvc\Router\Http\RegexSlash':
                $uri = $route['options']['regex'];
                
                break;
            
            default:
                $uri = '';
                
                break;
        }
        if(array_key_exists('defaults', $route['options'])) {
            $controller = isset($route['options']['defaults']['controller'])?$route['options']['defaults']['controller']:false;
            $action = isset($route['options']['defaults']['action'])?$route['options']['defaults']['action']:false;
        } else {
            $controller = false;
            $action = false;
        }


        $string = '';
        for ($cpt=0; $cpt <= $row; $cpt++) {
            $string .= '|&nbsp;&nbsp;&nbsp;&nbsp;';
        }


        if($controller) {
            // Gestion du package
            $package = current(array_filter(explode('/', $url)));
            if(empty($package)) {
                $package = "frontend";
            }
            $config = $this->getServiceManager()->get('Config');
            if(!empty($package) && array_key_exists($package, $config['design'])) {
                $design = implode('/', $config['design'][$package]);
            } else {
                $design = implode('/', $config['design']['frontend']);
            }

            $controllerName = str_replace(array('Controller', 'controller'), "", $controller);
            $reverseAlias = $this->_reverseAlias($controllerName);
            $explodeController = explode('\\', $reverseAlias);
            $template = __DIR__.'/../../../../../../design/' . $package . '/' . $design .'/'. $this->_getFileName(current($explodeController)) . '/' . $this->_getFileName(end($explodeController)) . '/' . $this->_getFileName($action) . '.phtml';
        } else {
            $template = "";
        }

        if(file_exists($template)) {
            
            // Parsing du template
            $keys = $this->parseTemplate($template);

            $partials = $this->parseForPartials($template);
            foreach ($partials as $partial) {
                $partialFile = $template = __DIR__.'/../../../../../../design/' . $package . '/' . $design .'/'.$partial;
                if(file_exists($partialFile)) {
                    $keys = array_merge($keys, $this->parseTemplate($partialFile));
                }
            }

            $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
            $pathTranslate = $options->getLanguagePath();

            $path = __DIR__.$pathTranslate.'categories/'.str_replace("\\", "", $controller);
            if(!file_exists($path)) {
                mkdir($path, true);
            }
            $data = array(
                'keys' => array_unique($keys), // On enleve les doublons
                'url' => trim($url."/".$key, '/'),
                'type' => $route['type'],
                'controller' => $controller,
                'action' => $action,
                'template' => $template,
            );
            file_put_contents($path.'/'.$action.'.json', json_encode($data));

        }

        if(is_array($route) && array_key_exists('child_routes', $route)) {
            foreach ($route['child_routes'] as $childKey => $childRoute) {
                $this->buildTreeRecursive($row + 1, $childKey, $childRoute, $url."/".$key);
            }
        }
    }

    /*
        Retourne le nom du controller a partir de son alias
    */
    protected function _reverseAlias($alias) {
        $config = $this->getServiceManager()->get('Config');
        $aliases = $config['controllers']['invokables'];
        if(array_key_exists($alias, $aliases)) {
            return $aliases[$alias];
        }
        return $alias;
    }

    /*
        factory pour passe de "JeSuisUnBG" à "je-suis-un-bg"
    */
    protected function _getFileName($file) {
        $return = "";
        $last = true; // Gestion des majuscules qui se suivent (on ne met pas de '-') et de la premiere majuscule
        foreach(str_split($file) as $car) {
            if(!$last && ord($car) >= 65 && ord($car) <= 90) {
                $return .= "-" . strtolower($car);
                $last = true;
            } else {
                $return .= strtolower($car);
                $last = false;
            }
        }
        return $return;
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

        $arborescence = $this->getArborescence();

        $options = $this->getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = $options->getLanguagePath();

        $translates = @include __DIR__.$pathTranslate.$data['locale'].'.php';

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
        $translates = array_merge($allKeysInKey, $translates);
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