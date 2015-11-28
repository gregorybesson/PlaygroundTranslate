<?php

namespace PlaygroundTranslate\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    protected $languagePath = '/../../../language/';

    public function setLanguagePath($languagePath)
    {
        $this->languagePath = $languagePath;
    }

    public function getLanguagePath()
    {
        return $this->languagePath;
    }
}
