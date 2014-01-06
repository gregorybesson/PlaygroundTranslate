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
        $translates = array("Impress" => "Mentions légales");

        $ts = new \PlaygroundTranslate\Service\Translate();

        $content = $ts->convertArrayToCSV($translates);
        $this->assertEquals('Impress;Mentions légales
',$content);

    }
}