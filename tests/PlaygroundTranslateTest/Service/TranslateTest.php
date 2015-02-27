<?php
namespace PlaygroundTranslateTest\Service;

use PlaygroundTranslateTest\Bootstrap;


class CategoryTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;


    public function setUp()
    {
        parent::setUp();
    }

    public function testConvertArrayToCSV()
    {
        $content = array();
        $content['toto'] = 'titi';
        $content['foo'] = 'bar';

        $service = new \PlaygroundTranslate\Service\Translate();
        $service->setServiceManager(Bootstrap::getServiceManager());

        $result = $service->convertArrayToCSV($content);
        $this->assertEquals($result, "toto;titi
foo;bar
");
    }

    public function testExport()
    {
        $service = new \PlaygroundTranslate\Service\Translate();
        $sm = Bootstrap::getServiceManager();
        $service->setServiceManager($sm);
        $data['locale'] = 'en_US';
        $result = $service->export($data);
        $this->assertEquals(getType($result), 'array');
        $this->assertEquals(count($result) > 0, true);
    }

    public function testReadLanguageFile()
    {
        $service = new \PlaygroundTranslate\Service\Translate();
        $service->setServiceManager(Bootstrap::getServiceManager());
        $locale = 'en_US';
        $result = $service->readLanguageFile($locale);
        $this->assertEquals(getType($result), 'array');
        $this->assertEquals(count($result) > 0, true);
    }

    public function testGetHistory()
    {
        $service = new \PlaygroundTranslate\Service\Translate();
        $service->setServiceManager(Bootstrap::getServiceManager());
        $locale = 'fr_FR';
        $filename = "fr_FR.php.20140130134237";
        $options = Bootstrap::getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = __DIR__.$options->getLanguagePath()."revisions/";
        
        touch($pathTranslate.$filename);
        $result = $service->getHistory(array(), $locale);
        unlink($pathTranslate.$filename);

        $this->assertEquals(count($result), 1);
        $result = array_keys($result);
        $this->assertEquals($result[0], "20140130134237");
    }
        
    public function testReadLanguagesFiles()
    {
        $service = new \PlaygroundTranslate\Service\Translate();
        $service->setServiceManager(Bootstrap::getServiceManager());
        $locale = 'en_US';
        $result = $service->readLanguagesFiles();
        $this->assertEquals(getType($result), 'array');
        $this->assertEquals(count($result) > 0, true);
    }

    public function testActiveTranslate()
    {
        $service = new \PlaygroundTranslate\Service\Translate();
        $service->setServiceManager(Bootstrap::getServiceManager());

        $options = Bootstrap::getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = __DIR__.$options->getLanguagePath()."";
        $filename = "en_US.php.tmp";
        $content = "<?php

/**
 * Adfab Connect
 *
 * translate file auto generated
 *
 */

return array(
    'import translate' => 'Import a new version of translations',
    'locale' => 'Langage',
    'translations' => 'translations'
);
";
        $ret = file_put_contents($pathTranslate.$filename, $content);
     
        $result = $service->activeTranslate('en_US');

        $this->assertEquals($result, true);
    }

    public function testWriteFile()
    {
        $service = new \PlaygroundTranslate\Service\Translate();
        $service->setServiceManager(Bootstrap::getServiceManager());

        $options = Bootstrap::getServiceManager()->get('playgroundtranslate_module_options');
        $locale = 'en_US';
        $content = array('import translate' => 'Import a new version of translations','locale' => 'Langage');
        

        $result = $service->writeFile('en_US', $content);

        $options = Bootstrap::getServiceManager()->get('playgroundtranslate_module_options');
        $pathTranslate = __DIR__.$options->getLanguagePath()."";
        $filename = "en_US.php.tmp";

        unlink($pathTranslate.$filename);

        $this->assertEquals(count($result) > 0, true);
    }
}   