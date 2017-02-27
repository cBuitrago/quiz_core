<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * AccountAppSettings
 * @ORM\Table(name="account_app_settings", indexes={@ORM\Index(name="FPK_account_info", columns={"FPK_account_info"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AccountAppSettings extends AbstractEntity 
{
    /**
     * @var AccountInfo
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AccountInfo", inversedBy="accountAppSettings")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FPK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;
    
    /**
     * @var text
     * @ORM\Column(name="settings", type="text", nullable=false)
     */
    private $settings;
    
    /**
     * @var string
     * @ORM\Column(name="metadata", type="string", length=512, nullable=false)
     */
    private $metadata;
    
    /**
     * @var string
     * @ORM\Column(name="minimum_version", type="string", length=16, nullable=false)
     */
    private $minVersion;
    
    /**
     * @var string
     * @ORM\Column(name="supported_languages", type="string", length=64, nullable=false)
     */
    private $supportedLanguages;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="modified_on", type="datetime", nullable=false)
     */
    private $modifiedOn;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function setSettings($settings) { $this->settings = $settings; return $this; }
    public function getSettings() { return $this->settings; }
    public function setMetadata($metadata) { $this->metadata = $metadata; return $this; }
    public function getMetadata() { return $this->metadata; }
    public function setMinVersion($minVersion) { $this->minVersion = $minVersion; return $this; }
    public function getMinVersion() { return $this->minVersion; }
    public function setSupportedLanguages($supportedLanguages) { $this->supportedLanguages = $supportedLanguages; return $this; }
    public function getSupportedLanguages() { return $this->supportedLanguages; }
    public function getModifiedOn() { return $this->modifiedOn; }
    public function getCreatedOn() { return $this->createdOn; }
    
    /** @ORM\PreUpdate */
    public function onPreUpdate()
    {
        $this->modifiedOn = new DateTime();
    }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->modifiedOn = new DateTime();
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->setSettings($requestData->settings);
        $this->setMetadata($requestData->metadata);
        $this->setMinVersion($requestData->minVersion);
        $this->setSupportedLanguages($requestData->supportedLanguages);
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if (isset($requestData->accountInfo))
            $this->setAccountInfo($requestData->accountInfo);
        if (isset($requestData->settings))
            $this->setSettings($requestData->settings);
        if (isset($requestData->metadata))
            $this->setMetadata($requestData->metadata);
        if (isset($requestData->minVersion))
            $this->setMinVersion($requestData->minVersion);
        if (isset($requestData->supportedLanguages))
            $this->setSupportedLanguages($requestData->supportedLanguages);
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->accountInfo = $this->getAccountInfo()->getId();
        $data->settings = $this->getSettings();
        $data->metadata = $this->getMetadata();
        $data->minVersion = $this->getMinVersion();
        $data->supportedLanguages = $this->getSupportedLanguages();
        $data->modifiedOn = $this->getModifiedOn()->getTimestamp();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->getAccountInfo()->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->getAccountInfo()->getId());
    }
}
