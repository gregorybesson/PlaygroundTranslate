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

class SiteCountryAdminController extends AbstractActionController
{

	protected $siteCountryService;

	protected $localeService;

	public function indexAction()
	{
		return new ViewModel();
	}

	public function listAction()
	{
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));
        $user = $this->zfcUserAuthentication()->getIdentity();
        if($user->getRole()->getRoleId()=="c_admin") {
            $siteCountries = $user->getSiteCountries();
        } elseif($user->getRole()->getRoleId()=="c_super_admin") {
            $siteCountries = $this->getSiteCountryService()->getSiteCountryMapper()->findAll();
        }
        return new ViewModel(compact("siteCountries", "locales"));
	}

	public function editActiveAction()
	{
		$siteCountryId = $this->getEvent()->getRouteMatch()->getParam('siteCountryId');
		$siteCountry = $this->getSiteCountryService()->getSiteCountryMapper()->findBy(array('id' => $siteCountryId));
		$siteCountry = $siteCountry[0];
		$siteCountry->setActive(!$siteCountry->getActive());
		$this->getSiteCountryService()->getSiteCountryMapper()->update($siteCountry);
		$this->redirect()->toRoute('admin');
	}

	public function getSiteCountryService()
    {
        if (null === $this->siteCountryService) {
            $this->siteCountryService = $this->getServiceLocator()->get('playgroundtranslate_sitecountry_service');
        }

        return $this->siteCountryService;
    }

    public function setSiteCountryService($siteCountryService)
    {
        $this->siteCountryService = $siteCountryService;

        return $this;
    }

    public function getLocaleService()
    {
        if (null === $this->localeService) {
            $this->localeService = $this->getServiceLocator()->get('playgroundtranslate_locale_service');
        }

        return $this->localeService;
    }

    public function setLocaleService($localeService)
    {
        $this->localeService = $localeService;

        return $this;
    }
}