<?php

namespace com\novaconcept\entity;

use com\novaconcept\utility\ApiConfig;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * Quiz
 *
 * @ORM\Table(name="quiz_group")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class QuizGroup extends AbstractEntity 
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NAME", type="string", nullable=false)
     */
    private $name;

    public function __construct()
    {
        
    }

    public function getId() { return $this->id; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getName() { return $this->name; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {

    }
    
    /** @ORM\PreUpdate */
    public function onPreUpdate()
    {

    }
 
    public function mapPostData($requestData) 
    {
        $this->setName($requestData->name);
    }
    
    public function mergePostData($requestData)
    {
        if ( isset($requestData->name) )
            $this->setName($requestData->name);
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
        {
            $includes = array();
        }
        
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->name = $this->getName();
                
        return $data;
    }
}