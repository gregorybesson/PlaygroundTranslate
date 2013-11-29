<?php

namespace PlaygroundTranslate\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcBase\Mapper\AbstractDbMapper;

use Citroen\Options\ModuleOptions;

class SiteCountry
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    /**
     * @var PlaygroundTranslate\Options\ModuleOptions
     */
    protected $options;


    /**
    * __construct
    * @param Doctrine\ORM\EntityManager $em
    * @param PlaygroundTranslate\Options\ModuleOptions $options
    *
    */
    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

    /**
    * findById : recupere l'entite en fonction de son id
    * @param int $id id de la company
    *
    * @return PlaygroundTranslate\Entity\Contact $contact
    */
    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
    * findBy : recupere des entites en fonction de filtre
    * @param array $array tableau de filtre
    *
    * @return collection $contacts collection de PlaygroundTranslate\Entity\Contact
    */
    public function findBy($array)
    {
        return $this->getEntityRepository()->findBy($array);
    }

    /**
    * insert : insert en base une entité contact
    * @param PlaygroundTranslate\Entity\Contact $contact contact
    *
    * @return PlaygroundTranslate\Entity\Contact $contact
    */
    public function insert($entity)
    {
        return $this->persist($entity);
    }

    /**
    * insert : met a jour en base une entité contact
    * @param PlaygroundTranslate\Entity\Contact $contact contact
    *
    * @return PlaygroundTranslate\Entity\Contact $contact
    */
    public function update($entity)
    {
        return $this->persist($entity);
    }

    /**
    * insert : met a jour en base une entité company et persiste en base
    * @param PlaygroundTranslate\Entity\Contact $entity contact
    *
    * @return PlaygroundTranslate\Entity\Contact $contact
    */
    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
    * findAll : recupere toutes les entites
    *
    * @return collection $contact collection de PlaygroundTranslate\Entity\Contact
    */
    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

     /**
    * remove : supprimer une entite contact
    * @param PlaygroundTranslate\Entity\Contact $contact Contact
    *
    */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
    * getEntityRepository : recupere l'entite contact
    *
    * @return PlaygroundTranslate\Entity\Contact $contact
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundTranslate\Entity\SiteCountry');
        }

        return $this->er;
    }
}