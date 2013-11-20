<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PlaygroundTranslate\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TranslateAdminController extends AbstractActionController
{
    public function indexAction()
    {
        $options = $this->getTranslateOptions();
        var_dump($options->getLocales());

        return new ViewModel();
    }


    public function getTranslateOptions()
    {
        return $this->getServiceLocator()->get('playgroundTranslate_module_options');
    }
}