<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * UserAccount
 * @ORM\Table(name="user_account")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserAccount extends AbstractEntity
{
    
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var UserInfo
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="userAccount")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;
    
    /**
     * @var AccountInfo
     * @ORM\ManyToOne(targetEntity="AccountInfo", inversedBy="userAccount")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;
    
    /**
     * @var DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    public function __construct() { }
    
    public function getID() { return $this->id; }
    public function setUserInfo($userInfo) { $this->userInfo = $userInfo; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function getCreatedOn() { return $this->createdOn; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->createdOn = new DateTime();
    }

    public function mapPostData($requestData) { return $this; }

    public function mergePostData($requestData) { return $this; }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->userInfo = $this->getUserInfo()->getId();
        $data->accountInfo = $this->getAccountInfo()->getId();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->accountInfo->getData();
        
        if (array_search('user_info', $includes) !== FALSE)
            $data->userInfo = $this->userInfo->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }

}
