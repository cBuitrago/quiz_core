<?php

namespace com\novaconcept\entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * SecurityReplay
 * @ORM\Table(name="security_replay")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SecurityReplay extends AbstractEntity 
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
     * @ORM\Column(name="for_ip_address", type="string", length=15, nullable=true)
     */
    private $forIpAddress;

    /**
     * @var string
     * @ORM\Column(name="http_method", type="string", nullable=false)
     */
    private $httpMethod;

    /**
     * @var string
     * @ORM\Column(name="endpoint", type="string", length=256, nullable=false)
     */
    private $endpoint;

    /**
     * @var string
     * @ORM\Column(name="signature", type="string", length=32, nullable=false)
     */
    private $signature;
    
    /**
     * @var string
     * @ORM\Column(name="nonce", type="string", length=32, nullable=false)
     */
    private $nonce;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;


    public function getId() { return $this->id; }
    public function setIpAddress($ipAddress) { $this->ipAddress = $ipAddress; return $this; }
    public function getIpAddress() { return $this->ipAddress; }
    public function setForIpAddress($forIpAddress) { $this->forIpAddress = $forIpAddress; return $this; }
    public function getForIpAddress() { return $this->forIpAddress; }
    public function setHttpMethod($httpMethod) { $this->httpMethod = $httpMethod; return $this; }
    public function getHttpMethod() { return $this->httpMethod; }
    public function setEndpoint($endpoint) { $this->endpoint = $endpoint; return $this; }
    public function getEndpoint() { return $this->endpoint; }
    public function setSignature($signature) { $this->signature = $signature; return $this; }
    public function getSignature() { return $this->signature; }
    public function setNonce($nonce) { $this->nonce = $nonce; return $this; }
    public function getNonce() { return $this->nonce; }
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
        $this->signature = $requestData->signature;
        $this->nonce = $requestData->nonce;
        
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
        
        $data->ipAddress = $this->ipAddress;
        $data->forIpAddress = $this->forIpAddress;
        $data->httpMethod = $this->httpMethod;
        $data->endpoint = $this->endpoint;
        $data->signature = $this->signature;
        $data->nonce = $this->nonce;
        $data->createdOn = $this->createdOn->getTimestamp();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
