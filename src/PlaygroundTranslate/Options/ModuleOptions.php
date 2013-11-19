<?php

namespace Citroen\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    protected $locale = null;

    protected $locales = null;
    protected $countries = null;


    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }


    public function setCountries($countries)
    {
        $this->countries = $countries;

        return $this;
    }

    public function getCountries()
    {
        return $this->countries;
    }


    public function getLocales()
    {
        return $this->locales;
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

}