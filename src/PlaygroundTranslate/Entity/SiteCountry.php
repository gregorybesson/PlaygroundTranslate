<?php

namespace PlaygroundTranslate\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="translate_site_country")
 */
class SiteCountry implements InputFilterAwareInterface
{

    protected $inputFilter;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * name
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * code
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    protected $code;
    
    /**
     * phase
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $phase;
    
    /**
     * active
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $active;

    /**
     * default
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $by_default = 0;


    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="PlaygroundTranslate\Entity\Locale")
     * @ORM\JoinTable(name="translate_sitecountry_locale",
     *      joinColumns={@ORM\JoinColumn(name="sitecountry_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="locale_id", referencedColumnName="id")}
     * )
     */
    protected $locales;

 

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @param string $id
     * @return SiteCountry
     */
    public function setId($id)
    {
        $this->id = (string) $id;

        return $this;
    }

    /**
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updated_at = new \DateTime("now");
    }
    
    /**
     * @return string $name
     */
    public function getName()
    {
    	return $this->name;
    }
    
    /**
     * @param string $name
     * @return SiteCountry
     */
    public function setName($name)
    {
    	$this->name = (string) $name;
    
    	return $this;
    }
    
    /**
     * @return string $code
     */
    public function getCode()
    {
    	return $this->code;
    }
    
    /**
     * @param string $code
     * @return SiteCountry
     */
    public function setCode($code)
    {
    	$this->code = (string) $code;
    
    	return $this;
    }

    /**
     * @return string $phase
     */
    public function getPhase()
    {
        return $this->phase;
    }
    
    /**
     * @param string $phase
     * @return SiteCountry
     */
    public function setPhase($phase)
    {
        $this->phase = (string) $phase;
    
        return $this;
    }

    /**
     * @return string $active
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * @param string $active
     * @return SiteCountry
     */
    public function setActive($active)
    {
        $this->active = (int) $active;
    
        return $this;
    }

     /**
     * @return string $default
     */
    public function getDefault()
    {
        return $this->by_default;
    }
    
    /**
     * @param string $default
     * @return SiteCountry
     */
    public function setDefault($default)
    {
        $this->by_default = (string) $default;
    
        return $this;
    }

    /**
     * @return PlaygroundTranslate\Entity\Locale $locales
     */
    public function getLocales()
    {
        return $this->locales;
    }
    
    /**
     * @param PlaygroundTranslate\Entity\Locale $locales
     * @return SiteCountry
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    
        return $this;
    }
    
    /**
     * @param PlaygroundTranslate\Entity\Locale $locale
     * @return SiteCountry
     */
    public function addLocale($locale)
    {
    	$this->locales[] = $locale;
    
    	return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param unknown_type $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param unknown_type $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getFlag()
    {
        return "/lib/flag/".strtolower($this->getCode());
    }

   

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
    	if (isset($data['name']) && $data['name'] != null) {
            $this->name = $data['name'];
        }
        if (isset($data['code']) && $data['code'] != null) {
        	$this->code = $data['code'];
        }
        if (isset($data['phase']) && $data['phase'] != null) {
        	$this->phase = $data['phase'];
        }
        if (isset($data['active']) && $data['active'] != null) {
        	$this->active = $data['active'];
        }
    }



    /**
    * setInputFilter
    * @param InputFilterInterface $inputFilter
    */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /**
    * getInputFilter
    *
    * @return  InputFilter $inputFilter
    */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
            $factory = new InputFactory();
        }
        return $this->inputFilter;
    }
}