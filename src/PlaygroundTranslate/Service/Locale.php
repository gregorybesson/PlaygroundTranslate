<?php

namespace PlaygroundTranslate\Service;

use PlaygroundTranslate\Entity\Locale as LocaleEntity;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Validator\NotEmpty;
use ZfcBase\EventManager\EventProvider;
use PlaygroundTranslate\Options\ModuleOptions;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Stdlib\ErrorHandler;

class Locale extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var localeMapper
     */
    protected $localeMapper;

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
     * This service is ready for create a locale
     *
     * @param  array  $data
     * @param  string $formClass
     *
     * @return \PlaygroundTranslate\Entity\Locale
     */
    public function create(array $data)
    {
        $locale = new LocaleEntity();
        $locale->populate($data);
        $entityManager = $this->getServiceManager()->get('playgroundtranslate_doctrine_em');

        $form = $this->getServiceManager()->get('playgroundtranslate_locale_form');

        $form->bind($locale);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $localeMapper = $this->getLocaleMapper();
        $locale = $localeMapper->insert($locale);

        return $locale;
    }

    /**
     *
     * This service is ready for edit a locale
     *
     * @param  array  $data
     * @param  string $locale
     * @param  string $formClass
     *
     * @return \PlaygroundTranslate\Entity\Locale
     */
    public function edit(array $data, $locale)
    {
        $entityManager = $this->getServiceManager()->get('playgroundtranslate_doctrine_em');

        $form  = $this->getServiceManager()->get('playgroundtranslate_locale_form');

        $form->bind($locale);

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        $locale = $this->getLocaleMapper()->update($locale);

        return $locale;
    }

    /**
     * getLocaleMapper
     *
     * @return LocaleMapper
     */
    public function getLocaleMapper()
    {
        if (null === $this->localeMapper) {
            $this->localeMapper = $this->getServiceManager()->get('playgroundtranslate_locale_mapper');
        }

        return $this->localeMapper;
    }

    /**
     * setLocaleMapper
     * @param  LocaleMapper $localeMapper
     *
     * @return PlaygroundTranslate\Entity\Locale Locale
     */
    public function setLocaleMapper($localeMapper)
    {
        $this->localeMapper = $localeMappers;

        return $this;
    }

    /**
     * setOptions
     * @param  ModuleOptions $options
     *
     * @return PlaygroundTranslate\Service\Locale $this
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