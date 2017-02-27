<?php

namespace com\novaconcept\entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * SecurityEvent
 * @ORM\Table(name="security_event")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SecurityEvent extends AbstractEntity 
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
     * @ORM\Column(name="ip_address", type="string", length=15, nullable=false)
     */
    private $ipAddress;

    /**
     * @var string
     * @ORM\Column(name="for_ip_adress", type="string", length=15, nullable=true)
     */
    private $forIpAdress;

    /**
     * @var string
     * @ORM\Column(name="http_method", type="string", nullable=false)
     */
    private $httpMethod;

    /**
     * @var string
     * @ORM\Column(name="endpoint", type="string", length=1024, nullable=false)
     */
    private $endpoint;

    /**
     * @var string
     * @ORM\Column(name="event_name", type="string", length=128, nullable=false)
     */
    private $eventName;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    public function getId() { return $this->id; }
    public function setIpAddress($ipAddress) { $this->ipAddress = $ipAddress; return $this; }
    public function getIpAddress() { return $this->ipAddress; }
    public function setForIpAdress($forIpAdress) { $this->forIpAdress = $forIpAdress; return $this; }
    public function getForIpAdress() { return $this->forIpAdress; }
    public function setHttpMethod($httpMethod) { $this->httpMethod = $httpMethod; return $this; }
    public function getHttpMethod() { return $this->httpMethod; }
    public function setEndpoint($endpoint) { $this->endpoint = $endpoint; return $this; }
    public function getEndpoint() { return $this->endpoint; }
    public function setEventName($eventName) { $this->eventName = $eventName; return $this; }
    public function getEventName() { return $this->eventName; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->createdOn = new DateTime();       
    }
    
    public function mapPostData($requestData)
    {
        $this->ipAddress = $requestData->ipAddress;
        $this->forIpAddress = $requestData->forIpAddress;
        $this->httpMethod = $requestData->httpMethod;
        $this->endpoint = $requestData->endpoint;
        $this->eventName = $requestData->eventName;
        
        return $this;
    }
    public function mergePostData($requestData)
    {
        return $this;
    }

    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->id;
        $data->forIpAddress = $this->forIpAdress;
        $data->httpMethod = $this->httpMethod;
        $data->endpoint = $this->endpoint;
        $data->eventName = $this->eventName;
        $data->createdOn = $this->createdOn->getTimestamp();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
