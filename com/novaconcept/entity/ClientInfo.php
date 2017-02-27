<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\ClientAccount;
use com\novaconcept\entity\ClientAuthentication;
use com\novaconcept\entity\ClientAuthorization;
use com\novaconcept\entity\ClientPermission;
use com\novaconcept\entity\ConfigModule;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ClientInfo
 * @ORM\Table(name="client_info")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ClientInfo extends AbstractEntity
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
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="modified_on", type="datetime", nullable=false)
     */
    private $modifiedOn;
    
    /**
     * @var ClientAuthentication
     * @ORM\OneToOne(targetEntity="ClientAuthentication", mappedBy="clientInfo")
     */
    private $clientAuthentication;
    
    /**
     * @var ConfigModule
     * @ORM\OneToOne(targetEntity="ConfigModule", mappedBy="clientInfo")
     */
    private $configModule;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ClientAuthorization", mappedBy="clientInfo", cascade={"all"}) 
     */
    private $clientAuthorizationCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ClientAccount", mappedBy="clientInfo", cascade={"all"}) 
     */
    private $clientAccountCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ClientPermission")
     * @ORM\JoinTable(name="client_authorization",
     *      joinColumns={@ORM\JoinColumn(name="FK_client_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_client_permission", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $clientPermissionCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AccountInfo")
     * @ORM\JoinTable(name="client_account",
     *      joinColumns={@ORM\JoinColumn(name="FK_client_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $accountInfoCollection;
    
    public function __construct()
    {
        $this->clientAccountCollection = new ArrayCollection();
        $this->clientAuthorizationCollection = new ArrayCollection();
        $this->clientPermissionCollection = new ArrayCollection();
        $this->accountInfoCollection = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getName() { return $this->name; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getDescription() { return $this->description; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getModifiedOn() { return $this->modifiedOn; }
    public function setClientAuthentication($clientAuthentication) { $this->clientAuthentication = $clientAuthentication; return $this; }
    public function getClientAuthentication() { return $this->clientAuthentication; }
    public function setConfigModule($configModule) { $this->configModule = $configModule; return $this; }
    public function getConfigModule() { return $this->configModule; }
    public function getClientAuthorizationCollection() { return $this->clientAuthorizationCollection; }
    public function getClientAccountCollection() { return $this->clientAccountCollection; }
    public function getClientPermissionCollection() { return $this->clientPermissionCollection; }
    public function getAccountInfoCollection() { return $this->accountInfoCollection; }
    
    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->createdOn = new DateTime();
        $this->modifiedOn = new DateTime();
    }
    /** @ORM\PreUpdate */
    public function modifiedOnPreUpdate()
    {
        $this->modifiedOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->setName($requestData->name);
        $this->setDescription($requestData->description);
        
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if(isset($requestData->name))
            $this->setName($requestData->name);
        
        if(isset($requestData->description))
            $this->setDescription($requestData->description);
        
        return $this;
    }
    
    /**
     * 
     * @param type $permissions
     * @return boolean
     */
    public function validatePermissions($permissions, $accountId = NULL)
    {
        $counter = 0;
        $hasPermission = FALSE;
        
        foreach ($permissions->getPermissionList() as $permission)
        {
            $hasPermission = FALSE;
            $this->clientAuthorizationCollection->first();
            while ( $this->clientAuthorizationCollection->current() != NULL )
            {
                if ($this->clientAuthorizationCollection->current()->getClientPermission()->getName() == "is_god")
                    return TRUE;
                
                if ($accountId == NULL)
                {
                    if ($this->clientAuthorizationCollection->current()->getClientPermission()->getName() == $permission)
                    {
                        $hasPermission = TRUE;
                        $counter += 1;
                        break;
                    }
                }
                else
                {
                   if ($this->clientAuthorizationCollection->current()->getClientPermission()->getName() == $permission 
                            && $this->clientAuthorizationCollection->current()->getAccountInfo()->getId() == $accountId)
                    {
                        $hasPermission = TRUE;
                        $counter += 1;
                        break;
                    } 
                }
                $this->clientAuthorizationCollection->next();
            }
            if ($hasPermission === FALSE)
                return FALSE;
        }
        
        if ($counter == count($permissions->getPermissionList()))
            return TRUE;
        
        return FALSE;
    }
    
    public function validateAccount($account)
    {
        if (is_int($this->accountInfoCollection->indexOf($account)))
            return TRUE;
        
        return FALSE;
    }
    
    public function hasPermission($permission, $accountId = NULL)
    {
        $this->clientAuthorizationCollection->first();
        while ( $this->clientAuthorizationCollection->current() != NULL )
        {
            if ($this->clientAuthorizationCollection->current()->getClientPermission()->getName() == "is_god")
                    return TRUE;
            
            if ($accountId == NULL)
            {
                if ($this->clientAuthorizationCollection->current()->getClientPermission()->getName() == $permission)
                    return TRUE;
            }
            else 
            {
                if ($this->clientAuthorizationCollection->current()->getClientPermission()->getName() == $permission 
                            && $this->clientAuthorizationCollection->current()->getAccountInfo()->getId() == $accountId)
                    return TRUE;
            }
            $this->clientAuthorizationCollection->next();
        }

        /*if ($this->clientPermissionCollection->indexOf($permission) !== FALSE)
            return TRUE;*/
        
        return FALSE;
    }
    
    public function getData($includes = NULL)
    {
        if ($includes === NULL)
            $includes = array();
        
        $data = new stdClass();
        $data->id = $this->getId();
        $data->name = $this->getName();
        $data->description = $this->getDescription();
        $data->clientAuthentication = $this->getClientAuthentication()->getData();
        $data->modifiedOn = $this->getModifiedOn()->getTimestamp();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();

        if (array_search('client_permission', $includes) !== FALSE)
        {
            $permissions = array();
            $this->clientPermissionCollection->first();
            while($this->clientPermissionCollection->current() != NULL)
            {
                array_push($permissions, $this->clientPermissionCollection->current()->getData());
                $this->clientPermissionCollection->next();
            }
            $data->clientPermission = $permissions;
        }
        
        if (array_search('client_authorization', $includes) !== FALSE)
        {
            $authorizations = array();
            $this->clientAuthorizationCollection->first();
            while($this->clientAuthorizationCollection->current() != NULL)
            {
                array_push($authorizations, $this->clientAuthorizationCollection->current()->getData());
                $this->clientAuthorizationCollection->next();
            }
            $data->clientAuthorization = $authorizations;
        }
        
        if (array_search('config_module', $includes) !== FALSE)
        {
            $configModule = NULL;
            if ($this->configModule != NULL)
                $configModule = $this->configModule->getData();
            $data->configModule = $configModule;
        }
        
        if (array_search('client_account', $includes) !== FALSE)
        {
            $clientAccount = array();
            $this->clientAccountCollection->first();
            while($this->clientAccountCollection->current() != NULL)
            {
                array_push($clientAccount, $this->clientAccountCollection->current()->getData());
                $this->clientAccountCollection->next();
            }
            $data->clientAccount = $clientAccount;
        }
        
        if (array_search('account_info', $includes) !== FALSE)
        {
            $accountInfo = array();
            $this->accountInfoCollection->first();
            while($this->accountInfoCollection->current() != NULL)
            {
                array_push($accountInfo, $this->accountInfoCollection->current()->getData());
                $this->accountInfoCollection->next();
            }
            $data->accountInfo = $accountInfo;
        }

        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}