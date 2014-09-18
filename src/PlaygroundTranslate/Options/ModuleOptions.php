<?php

namespace PlaygroundTranslate\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    protected $languagePath = '/../../../language/';

    protected $times;

    public function setLanguagePath($languagePath)
    {
        $this->languagePath = $languagePath;
    }

    public function getLanguagePath()
    {
        return $this->languagePath;
    }

    public function getTime()
    {
    	if(empty($this->time)) {
			$this->time = time();
    	}
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }
}