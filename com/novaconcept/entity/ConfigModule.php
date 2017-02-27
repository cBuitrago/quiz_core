<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\ClientInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ConfigModule
 * @ORM\Table(name="config_module")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ConfigModule extends AbstractEntity 
{
    /**
     * @var ClientInfo
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="ClientInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FPK_id", referencedColumnName="PK_id")
     * })
     */
    private $clientInfo;
    
    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;
    
    /**
     * @var string
     * @ORM\Column(name="endpoint", type="string", length=256, nullable=false)
     */
    private $endpoint;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ConfigEvent", mappedBy="configModule", cascade={"all"}) 
     */
    private $configEventCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ConfigListener", mappedBy="configModule", cascade={"all"}) 
     */
    private $configListenerCollection;
    
    public function __construct()
    {
        $this->configEventCollection = new ArrayCollection();
        $this->configListenerCollection = new ArrayCollection();
    }
    
    public function setClientInfo($clientInfo) { $this->clientInfo = $clientInfo; return $this; }
    public function getClientInfo() { return $this->clientInfo; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setEndpoint($endpoint) { $this->endpoint = $endpoint; return $this; }
    public function getEndpoint() { return $this->endpoint; }
    public function getConfigEventCollection() { return $this->configEventCollection; }
    public function getConfigListenerCollection() { return $this->configListenerCollection; }

    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->isActive = TRUE;
    }
    
    public function mapPostData($requestData)
    {
        $this->endpoint = $requestData->endpoint;
        
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if (isset($requestData->isActive))
            $this->isActive = $requestData->isActive;
        
        if (isset($requestData->endpoint))
            $this->endpoint = $requestData->endpoint;
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ($includes === NULL)
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->clientInfo->getId();
        $data->isActive = $this->isActive;
        $data->endpoint = $this->endpoint;
        
        if (array_search('client_info', $includes) !== FALSE)
            $data->clientInfo = $this->clientInfo->getData();
        
        if (array_search('config_event', $includes) !== FALSE)
        {
            $configEvent = array();
            $this->configEventCollection->first();
            while ($this->configEventCollection->current() != NULL)
            {
                array_push($configEvent, $this->configEventCollection->current()->getData());
                $this->configEventCollection->next();
            }
            $data->configEvent = $configEvent;
        }
        
        if (array_search('config_listener', $includes) !== FALSE)
        {
            $configListener = array();
            $this->configListenerCollection->first();
            while ($this->configListenerCollection->current() != NULL)
            {
                array_push($configListener, $this->configListenerCollection->current()->getData());
                $this->configListenerCollection->next();
            }
            $data->configListenerCollection = $configListener;
        }
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->clientInfo->getId());
    }
}
