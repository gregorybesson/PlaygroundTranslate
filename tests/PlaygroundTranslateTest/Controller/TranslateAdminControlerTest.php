<?php

namespace PlaygroundTranslateTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use PlaygroundDesignTest\Bootstrap;
use PlaygroundDesign\Entity\Company as CompanyEntity;

class TranslateAdminControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

   /**
    * @var $themeMapper mapper de l'entity theme
    */
    protected $CompanyMapper;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testexportTranslate()
    {
        $content = '';
       
        $ts = new \PlaygroundTranslate\Service\Translate();

        $translates = array("Impress" => "Mentions légales");
        $content = $ts->convertArrayToCSV($translates);
        $this->assertEquals('Impress;Mentions légales
',$content);

         $translates = array("Impress" => "Odtlačok");
        $content = $ts->convertArrayToCSV($translates);
        $this->assertEquals('Impress;Odtlačok
',$content);

         $translates = array("Impress" => "版本說明 Bǎnběn shuōmíng");
        $content = $ts->convertArrayToCSV($translates);
        $this->assertEquals('Impress;版本說明 Bǎnběn shuōmíng
',$content);

    }
}