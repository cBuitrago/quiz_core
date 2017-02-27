<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\ClientInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ClientAccount
 * @ORM\Table(name="client_account")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ClientAccount extends AbstractEntity
{
    
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var ClientInfo
     * @ORM\ManyToOne(targetEntity="ClientInfo", inversedBy="clientAccount")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_client_info", referencedColumnName="PK_id")
     * })
     */
    private $clientInfo;
    
    /**
     * @var AccountInfo
     * @ORM\ManyToOne(targetEntity="AccountInfo", inversedBy="clientAccount")
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
    public function setClientInfo($clientInfo) { $this->clientInfo = $clientInfo; return $this; }
    public function getClientInfo() { return $this->clientInfo; }
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
        $data->id = $this->id;
        $data->createdOn = $this->createdOn->getTimestamp();
        $data->accountInfo = $this->accountInfo->getId();
        $data->clientInfo = $this->clientInfo->getId();
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->accountInfo->getData();
        
        if (array_search('client_info', $includes) !== FALSE)
            $data->clientInfo = $this->clientInfo->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
    
}
