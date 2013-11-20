<?php

namespace PlaygroundTranslate\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    protected $locales = null;
 
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