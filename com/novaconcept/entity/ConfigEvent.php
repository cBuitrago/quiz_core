<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\ConfigModule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ConfigEvent
 * @ORM\Table(name="config_event")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ConfigEvent extends AbstractEntity 
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
     * @var ConfigModule
     * @ORM\ManyToOne(targetEntity="ConfigModule", inversedBy="configEventCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_module", referencedColumnName="FPK_id")
     * })
     */
    private $configModule;
    
    /**
     * @var string
     * @ORM\Column(name="tag", type="string", length=256, nullable=false)
     */
    private $tag;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ConfigListener", mappedBy="configEvent", cascade={"all"}) 
     */
    private $configListenerCollection;
    
    public function __construct()
    {
        $this->configListenerCollection = new ArrayCollection();
    }
    
    public function getId() { return $this->id; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setConfigModule($configModule) { $this->configModule = $configModule; return $this; }
    public function getConfigModule() { return $this->configModule; }
    public function setTag($tag) { $this->tag = $tag; return $this; }
    public function getTag() { return $this->tag; }
    public function getConfigListenerCollection() { return $this->configListenerCollection; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->isActive = TRUE;
    }
    
    public function mapPostData($requestData)
    {
        $this->tag = $requestData->tag;
        
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if (isset($requestData->isActive))
            $this->isActive = $requestData->isActive;
        
        if (isset($requestData->tag))
            $this->tag = $requestData->tag;
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ($includes === NULL)
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->id;
        $data->isActive = $this->isActive;
        $data->tag = $this->tag;
        
        if (array_search('config_module', $includes) !== FALSE)
                $data->configModule = $this->configModule->getData();
        
        if (array_search('config_listener', $includes) !== FALSE)
        { 
            $configListener = array();
            $this->configListenerCollection->first();
            while($this->configListenerCollection->current() != NULL)
            {
                array_push($configListener, $this->configListenerCollection->current()->getData());
                $this->configListenerCollection->next();
            }
            $data->configListener = $configListener;
        }
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
