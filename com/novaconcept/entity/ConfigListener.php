<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\ConfigEvent;
use com\novaconcept\entity\ConfigModule;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ConfigListener
 * @ORM\Table(name="config_listener")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ConfigListener extends AbstractEntity 
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var ConfigEvent
     * @ORM\ManyToOne(targetEntity="ConfigEvent", inversedBy="configListenerCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_config_event", referencedColumnName="PK_id")
     * })
     */
    private $configEvent;

    /**
     * @var ConfigModule
     * @ORM\ManyToOne(targetEntity="ConfigModule", inversedBy="configListenerCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_config_module", referencedColumnName="FPK_id")
     * })
     */
    private $configModule;

    /**
     * @var string
     * @ORM\Column(name="callback_path", type="string", length=512, nullable=false)
     */
    private $callbackPath;

    public function getId() { return $this->id; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setConfigEvent($configEvent) { $this->configEvent = $configEvent; return $this; }
    public function getConfigEvent() { return $this->configEvent; }
    public function setConfigModule($configModule) { $this->configModule = $configModule; return $this; }
    public function getConfigModule() { return $this->configModule; }
    public function setCallbackPath($callbackPath) { $this->callbackPath = $callbackPath; return $this; }
    public function getCallbackPath() { return $this->callbackPath; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->isActive = TRUE;
    }
    
    public function mapPostData($requestData)
    {
        $this->callbackPath = $requestData->callbackPath;
        
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if (isset($requestData->isActive))
            $this->isActive = $requestData->isActive;
        
        if (isset($requestData->callbackPath))
            $this->callbackPath = $requestData->callbackPath;
        
        return $this;
    }

    public function getData($includes = NULL)
    {
        if ($includes === NULL)
            $includes = array();
        
        $data = new stdClass();
        $data->id = $this->id;
        $data->isActive = $this->isActive;
        $data->callbackPath = $this->callbackPath;
        
        if (array_search('config_event', $includes) !== FALSE)
                $data->configEvent = $this->configEvent->getData();
        
        if (array_search('config_module', $includes) !== FALSE)
                $data->configModule = $this->configModule->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
