<?php

namespace com\novaconcept\entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * UserPermission
 * @ORM\Table(name="user_permission")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserPermission extends AbstractEntity 
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
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserAuthorization", mappedBy="userPermission", cascade={"all"}) 
     */
    private $userAuthorizationCollection;
    
    public function __construct() 
    {
        $this->userAuthorizationCollection = new ArrayCollection();
    }

    public function setId($id) { $this->id = $id; return $this; }
    public function getId() { return $this->id; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getName() { return $this->name; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getDescription() { return $this->description; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getUserAuthorizationCollection() { return $this->userAuthorizationCollection; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->setIsActive(TRUE);
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->name = $requestData->name;
        $this->description = $requestData->description;
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if (isset($requestData->name))
            $this->name = $requestData->name;
        
        if (isset($requestData->description))
            $this->description = $requestData->description;
        
        if (isset($requestData->isActive))
            $this->isActive = $requestData->isActive;
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->id;
        $data->name = $this->name;
        $data->description = $this->description;
        $data->isActive = $this->isActive;
        $data->createdOn = $this->createdOn->getTimestamp();
        
        if (array_search('user_authorization', $includes) !== FALSE)
        {
            $userAuthorization = array();
            $this->userAuthorizationCollection->first();
            while($this->userAuthorizationCollection->current() != NULL)
            {
                array_push($userAuthorization, $this->userAuthorizationCollection->current()->getData());
                $this->userAuthorizationCollection->next();
            }
            $data->userAuthorization = $userAuthorization;
        }
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
