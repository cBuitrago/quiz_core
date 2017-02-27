<?php

namespace com\novaconcept\entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ClientPermission
 * @ORM\Table(name="client_permission")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ClientPermission extends AbstractEntity 
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    public function getId() { return $this->id; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getName() { return $this->name; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getDescription() { return $this->description; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function getCreatedOn() { return $this->createdOn; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->isActive = TRUE;
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->setName($requestData->name);
        $this->setDescription($requestData->description);
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if ( isset($requestData->name) )
            $this->setName($requestData->name);
        
        if ( isset($requestData->description) )
            $this->setDescription($requestData->description);
        
        if ( isset($requestData->isActive) )
            $this->setIsActive($requestData->isActive);
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ($includes === NULL)
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->id;
        $data->name = $this->name;
        $data->description = $this->description;
        $data->isActive = $this->isActive;
        $data->createdOn = $this->createdOn->getTimestamp();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
