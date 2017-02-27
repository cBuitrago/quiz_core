<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountBilling;
use com\novaconcept\entity\AccountConfig;
use com\novaconcept\entity\AccountContact;
use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * AccountInfo
 * @ORM\Table(name="account_info", indexes={@ORM\Index(name="FK_account_company", columns={"FK_account_company"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AccountInfo extends AbstractEntity 
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
     * @var string
     * @ORM\Column(name="name", type="string", length=512, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;
    
    /**
     * @var AccountBilling
     * @ORM\OneToOne(targetEntity="AccountBilling", mappedBy="accountInfo", cascade={"all"})
     **/
    private $accountBilling;
    
    /**
     * @var AccountAppSettings
     * @ORM\OneToOne(targetEntity="AccountAppSettings", mappedBy="accountInfo", cascade={"all"})
     **/
    private $accountAppSettings;
    
    /**
     * @var AccountConfig
     * @ORM\OneToOne(targetEntity="AccountConfig", mappedBy="accountInfo", cascade={"all"})
     **/
    private $accountConfig;

    /**
     * @var DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AccountContact", mappedBy="accountInfo", cascade={"all"})
     **/
    private $accountContactCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="DepartmentInfo", mappedBy="accountInfo", cascade={"all"})
     **/
    private $departmentInfoCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserAuthorization", mappedBy="accountInfo", cascade={"all"}) 
     */
    private $userAuthorizationCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ClientAuthorization", mappedBy="accountInfo", cascade={"all"}) 
     */
    private $clientAuthorizationCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ClientAccount", mappedBy="accountInfo", cascade={"all"}) 
     */
    private $clientAccountCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserAccount", mappedBy="accountInfo", cascade={"all"}) 
     */
    private $userAccountCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ClientInfo")
     * @ORM\JoinTable(name="client_account",
     *      joinColumns={@ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_client_info", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $clientInfoCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="UserInfo")
     * @ORM\JoinTable(name="user_account",
     *      joinColumns={@ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $userInfoCollection;
    
    public function __construct() 
    {
        $this->accountContactCollection = new ArrayCollection();
        $this->departmentInfoCollection = new ArrayCollection();
        $this->clientInfoCollection = new ArrayCollection();
        $this->clientAccountCollection = new ArrayCollection();
        $this->userAccountCollection = new ArrayCollection();
        $this->userInfoCollection = new ArrayCollection();
        $this->userAuthorizationCollection = new ArrayCollection();
        $this->clientAuthorizationCollection = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getName() { return $this->name; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getDescription() { return $this->description; }
    public function setAccountBilling($accountBilling) { $this->accountBilling = $accountBilling; return $this; }
    public function getAccountBilling() { return $this->accountBilling; }
    public function setAccountAppSettings($accountAppSettings) { $this->accountAppSettings = $accountAppSettings; return $this; }
    public function getAccountAppSettings() { return $this->accountAppSettings; }
    public function setAccountConfig($accountConfig) { $this->accountConfig = $accountConfig; return $this; }
    public function getAccountConfig() { return $this->accountConfig; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getAccountContactCollection() { return $this->accountContactCollection; }
    public function getDepartmentInfoCollection() { return $this->departmentInfoCollection; }
    public function getClientInfoCollection() { return $this->clientInfoCollection; }
    public function getUserAccountCollection() { return $this->userAccountCollection; }
    public function getClientAccountCollection() { return $this->clientAccountCollection; }
    public function getUserInfoCollection() { return $this->userInfoCollection; }
    public function getUserAuthorizationCollection() { return $this->userAuthorizationCollection; }
    public function getClientAuthorizationCollection() { return $this->clientAuthorizationCollection; }

    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->setIsActive(TRUE);
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
        if (isset($requestData->name))
            $this->setName($requestData->name);
        
        if (isset($requestData->description))
            $this->setDescription($requestData->description);
        
        if (isset($requestData->isActive))
            $this->setDescription($requestData->isActive);
        
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
        
        if (array_search('account_billing', $includes) !== FALSE)
            $data->accountBilling = $this->accountBilling->getData();
        
        if (array_search('account_app_settings', $includes) !== FALSE)
            $data->accountAppSettings = $this->accountAppSettings->getData();
        
        if (array_search('department_info', $includes) !== FALSE )
        {
            $department = array();
            $this->departmentInfoCollection->first();
            while($this->departmentInfoCollection->current() != NULL)
            {
                array_push($department, $this->departmentInfoCollection->current()->getData());
                $this->departmentInfoCollection->next();
            }
            $data->departmentInfo = $department;
        }
        
        if (array_search('account_contact', $includes) !== FALSE)
        {
            $contact = array();
            $this->accountContactCollection->first();
            while($this->accountContactCollection->current() != NULL)
            {
                array_push($contact, $this->accountContactCollection->current()->getData());
                $this->accountContactCollection->next();
            }
            $data->accountContact = $contact;
        }
        
        if (array_search('user_account', $includes) !== FALSE)
        {
            $userAccount = array();
            $this->userAccountCollection->first();
            while($this->userAccountCollection->current() != NULL)
            {
                array_push($userAccount, $this->userAccountCollection->current()->getData());
                $this->userAccountCollection->next();
            }
            $data->userAccount = $userAccount;
        }
        
        if (array_search('user_info', $includes) !== FALSE)
        {
            $userInfo = array();
            $this->userInfoCollection->first();
            while($this->userInfoCollection->current() != NULL)
            {
                array_push($userInfo, $this->userInfoCollection->current()->getData());
                $this->userInfoCollection->next();
            }
            $data->userInfo = $userInfo;
        }
        
        if (array_search('user_authorization', $includes) !== FALSE)
        {
            $userAuthorization = array();
            $this->userAuthorizationCollection->first();
            while($this->userAuthorizationCollection->current() != NULL)
            {
                array_push($userInfo, $this->userAuthorizationCollection->current()->getData());
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
