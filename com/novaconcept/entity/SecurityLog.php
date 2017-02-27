<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * SecurityLog
 * @ORM\Table(name="security_log", indexes={@ORM\Index(name="FK_user", columns={"FK_user"}), @ORM\Index(name="FK_client", columns={"FK_client"}), @ORM\Index(name="FK_client_2", columns={"FK_client"}), @ORM\Index(name="FK_user_2", columns={"FK_user"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SecurityLog extends AbstractEntity 
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
     * @var integer
     * @ORM\Column(name="response", type="integer", nullable=false)
     */
    private $response;

    /**
     * @var float
     * @ORM\Column(name="execution_time", type="float", precision=10, scale=0, nullable=false)
     */
    private $executionTime;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    /**
     * @var UserInfo
     * @ORM\ManyToOne(targetEntity="UserInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;

    /**
     * @var ClientInfo
     * @ORM\ManyToOne(targetEntity="ClientInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_client", referencedColumnName="PK_id")
     * })
     */
    private $clientInfo;

    public function getId() { return $this->id; }
    public function setIpAddress($ipAddress) { $this->ipAddress = $ipAddress; return $this; }
    public function getIpAddress() { return $this->ipAddress; }
    public function setForIpAddress($forIpAddress) { $this->forIpAddress = $forIpAddress; return $this; }
    public function getForIpAddress() { return $this->forIpAddress; }
    public function setHttpMethod($httpMethod) { $this->httpMethod = $httpMethod; return $this; }
    public function getHttpMethod() { return $this->httpMethod; }
    public function setEndpoint($endpoint) { $this->endpoint = $endpoint; return $this; }
    public function getEndpoint() { return $this->endpoint; }
    public function setResponse($response) { $this->response = $response; return $this; }
    public function getResponse() { return $this->response; }
    public function setExecutionTime($executionTime) { $this->executionTime = $executionTime; return $this; }
    public function getExecutionTime() { return $this->executionTime; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function setUserInfo($userInfo = null) { $this->userInfo = $userInfo; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    public function setClientInfo($clientInfo) { $this->clientInfo = $clientInfo; return $this; }
    public function getClientInfo() { return $this->clientInfo; }
    
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
        $this->response = $requestData->response;
        $this->executionTime = $requestData->executionTime;
        
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
        
        $this->ipAddress = $this->clientInfo->ipAddress;
        $this->forIpAddress = $this->forIpAddress;
        $this->httpMethod = $this->httpMethod;
        $this->endpoint = $this->endpoint; 
        $this->response = $this->response;
        $this->executionTime = $this->executionTime;
        $this->createdOn = $this->createdOn->getTimestamp();
        
        if (array_search('user_info', $includes) !== FALSE)
            $this->userInfo = $this->userInfo->getData ();
        
        if (array_search('client_info', $includes) !== FALSE)
            $this->clientInfo = $this->clientInfo->getData ();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
