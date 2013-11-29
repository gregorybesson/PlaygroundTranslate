<?php

namespace PlaygroundTranslate\Service;

use PlaygroundTranslate\Entity\SiteCountry as SiteCountryEntity;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Validator\NotEmpty;
use ZfcBase\EventManager\EventProvider;
use Citroen\Options\ModuleOptions;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Stdlib\ErrorHandler;

class SiteCountry extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var siteCountryMapper
     */
    protected $siteCountryMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    /**
     *
     * This service is ready for create a SiteCountry
     *
     * @param  array  $data
     * @param  string $formClass
     *
     * @return \PlaygroundTranslate\Entity\SiteCountry
     */
    public function create(array $data)
    {
        $siteCountry = new SiteCountry();
        $siteCountry->populate($data);
        $entityManager = $this->getServiceManager()->get('playgroundtranslate_doctrine_em');

        $form = $this->getServiceManager()->get('playgroundtranslate_sitecountry_form');

        $form->bind($siteCountry);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $siteCountryMapper = $this->getSiteCountryMapper();
        $siteCountry = $siteCountryMapper->insert($siteCountry);

        return $siteCountry;
    }

    /**
     *
     * This service is ready for edit a SiteCountry
     *
     * @param  array  $data
     * @param  string $siteCountry
     * @param  string $formClass
     *
     * @return \Citroen\Entity\SiteCountry
     */
    public function edit(array $data, $siteCountry)
    {
        $entityManager = $this->getServiceManager()->get('playgroundtranslate_doctrine_em');

        $form  = $this->getServiceManager()->get('playgroundtranslate_sitecountry_form');

        $form->bind($siteCountry);

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        $siteCountry = $this->getCompanyMapper()->update($siteCountry);

        return $siteCountry;
    }

    /**
     * getSiteCountryMapper
     *
     * @return SiteCountryMapper
     */
    public function getSiteCountryMapper()
    {
        if (null === $this->siteCountryMapper) {
            $this->siteCountryMapper = $this->getServiceManager()->get('playgroundtranslate_sitecountry_mapper');
        }

        return $this->siteCountryMapper;
    }

    /**
     * setSiteCountryMapper
     * @param  SiteCountryMapper $siteCountryMapper
     *
     * @return Citroen\Entity\SiteCountryMapper SiteCountryMapper
     */
    public function setSiteCountryMapper($siteCountryMapper)
    {
        $this->siteCountryMapper = $siteCountryMapper;

        return $siteCountryMapper;
    }

    /**
     * setOptions
     * @param  ModuleOptions $options
     *
     * @return Citroen\Service\SiteCountry $this
     */
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * getOptions
     *
     * @return ModuleOptions $optins
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundtranslate_module_options'));
        }

        return $this->options;
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