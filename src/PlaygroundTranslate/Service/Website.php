<?php

namespace PlaygroundTranslate\Service;

use PlaygroundTranslate\Entity\Website as WebsiteEntity;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Validator\NotEmpty;
use ZfcBase\EventManager\EventProvider;
use Citroen\Options\ModuleOptions;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Stdlib\ErrorHandler;

class Website extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var websiteMapper
     */
    protected $websiteMapper;

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
     * This service is ready for create a Website
     *
     * @param  array  $data
     * @param  string $formClass
     *
     * @return \PlaygroundTranslate\Entity\Website
     */
    public function create(array $data)
    {
        $website = new WebsiteEntity();
        $website->populate($data);
        $entityManager = $this->getServiceManager()->get('playgroundtranslate_doctrine_em');

        $form = $this->getServiceManager()->get('playgroundtranslate_website_form');

        $form->bind($website);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $websiteyMapper = $this->getWebsiteMapper();
        $website = $websiteyMapper->insert($website);

        return $website;
    }

    /**
     *
     * This service is ready for edit a Website
     *
     * @param  array  $data
     * @param  string $website
     * @param  string $formClass
     *
     * @return \Citroen\Entity\Website
     */
    public function edit(array $data, $website)
    {
        $entityManager = $this->getServiceManager()->get('playgroundtranslate_doctrine_em');

        $form  = $this->getServiceManager()->get('playgroundtranslate_website_form');

        $form->bind($website);

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        $website = $this->getWebsiteMapper()->update($website);

        return $website;
    }

    /**
     * getWebsiteMapper
     *
     * @return websiteMapper
     */
    public function getWebsiteMapper()
    {
        if (null === $this->websiteMapper) {
            $this->websiteMapper = $this->getServiceManager()->get('playgroundtranslate_website_mapper');
        }

        return $this->websiteMapper;
    }

    /**
     * setWebsiteMapper
     * @param  Mapper/Website $websiteMapper
     *
     * @return Citroen\Entity\WebsiteMapper websiteMapper
     */
    public function setWebsiteMapper($websiteMapper)
    {
        $this->websiteMapper = $websiteMapper;

        return $siteCountryMapper;
    }

    /**
     * setOptions
     * @param  ModuleOptions $options
     *
     * @return PlaygroundTranslate\Service\Website $this
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