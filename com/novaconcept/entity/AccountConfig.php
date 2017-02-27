<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * AccountBilling
 * @ORM\Table(name="account_config", indexes={@ORM\Index(name="FPK_account_info", columns={"FPK_account_info"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AccountConfig extends AbstractEntity 
{
    /**
     * @var AccountInfo
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AccountInfo", inversedBy="accountConfig")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FPK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;
    
    /** 
     * @var integer
     * @ORM\Column(name="login_attempt_limit", type="integer", nullable=false) 
     */
    private $loginAttemptLimit;
    
    /** 
     * @var integer
     * @ORM\Column(name="password_life_cycle", type="integer", nullable=false) 
     */
    private $passwordLifeCycle;
    
    /** 
     * @var integer
     * @ORM\Column(name="default_items_per_page", type="integer", nullable=false) 
     */
    private $defaultItemsPerPage;
    
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function setLoginAttemptLimit($loginAttemptLimit) { $this->loginAttemptLimit = $loginAttemptLimit; return $this; }
    public function getLoginAttemptLimit() { return $this->loginAttemptLimit; }
    public function setPasswordLifeCycle($passwordLifeCycle) { $this->passwordLifeCycle = $passwordLifeCycle; return $this; }
    public function getPasswordLifeCycle() { return $this->passwordLifeCycle; }
    public function setDefaultItemsPerPage($defaultItemsPerPage) { $this->defaultItemsPerPage = $defaultItemsPerPage; return $this; }
    public function getDefaultItemsPerPage() { return $this->defaultItemsPerPage; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->loginAttemptLimit = 5;
        $this->passwordLifeCycle = 7776000;
        $this->defaultItemsPerPage = 20;
    }

    public function mapPostData($requestData)
    {
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if (isset($requestData->loginAttemptLimit))
            $this->loginAttemptLimit = $requestData->loginAttemptLimit;
        if (isset($requestData->passwordLifeCycle))
            $this->passwordLifeCycle = $requestData->passwordLifeCycle;
        if (isset($requestData->defaultItemsPerPage))
            $this->defaultItemsPerPage = $requestData->defaultItemsPerPage;
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->accountInfo->getId();
        $data->loginAttemptLimit = $this->loginAttemptLimit;
        $data->passwordLifeCycle = $this->passwordLifeCycle;
        $data->defaultItemsPerPage = $this->defaultItemsPerPage;
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->accountInfo->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->accountInfo->getId());
    }
}
