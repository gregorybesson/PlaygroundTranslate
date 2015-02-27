<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PlaygroundTranslate\View\Helper;

use Zend\I18n\Exception;
use Zend\View\Helper\AbstractHelper;
use Zend\I18n\View\Helper\Translate as ZendTranslate;

/**
 * View helper for translating messages.
 */
class Translate extends ZendTranslate
{

    /**
     * Translate a message
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @throws Exception\RuntimeException
     * @return string
     */
    public function __invoke($message, $textDomain = null, $locale = null, $decoration = false)
    {
    	//$this->cacheTranslate($message);

        $translator = $this->getTranslator();
        if (null === $translator) {
            throw new Exception\RuntimeException('Translator has not been set');
        }
        if (null === $textDomain) {
            $textDomain = $this->getTranslatorTextDomain();
        }
        if(!$decoration) {
            return $translator->translate($message, $textDomain, $locale);
        }
        else {
            return '<!-- traduction-key:'.$message.' --> '.$translator->translate($message, $textDomain, $locale).' <!-- /traduction-key -->';
        }
    }

}
