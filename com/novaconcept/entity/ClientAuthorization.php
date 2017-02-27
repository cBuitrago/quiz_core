<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\ClientPermission;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ClientAuthorization
 * @ORM\Table(name="client_authorization", indexes={@ORM\Index(name="FK_client_info", columns={"FK_client_info"}), @ORM\Index(name="FK_client_permission", columns={"FK_client_permission"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ClientAuthorization extends AbstractEntity 
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ClientPermission
     * @ORM\ManyToOne(targetEntity="ClientPermission")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_client_permission", referencedColumnName="PK_id")
     * })
     */
    private $clientPermission;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    /**
     * @var ClientInfo
     * @ORM\ManyToOne(targetEntity="ClientInfo", inversedBy="clientAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_client_info", referencedColumnName="PK_id")
     * })
     */
    private $clientInfo;
    
    /**
     * @var AccountInfo
     * @ORM\ManyToOne(targetEntity="AccountInfo", inversedBy="clientAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;

    public function getId() { return $this->id; }
    public function setClientPermission($clientPermission) { $this->clientPermission = $clientPermission; return $this; }
    public function getClientPermission() { return $this->clientPermission; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function setClientInfo($clientInfo) { $this->clientInfo = $clientInfo; return $this; }
    public function getClientInfo() { return $this->clientInfo; }
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
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
        $data->id = $this->getId();
        $data->accountInfo = $this->getAccountInfo()->getId();
        $data->clientInfo = $this->clientInfo->getId();
        $data->clientPermission = $this->getClientPermission()->getId();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->accountInfo->getData();
        
        if (array_search('client_info', $includes) !== FALSE)
            $data->clientInfo = $this->clientInfo->getData();
        
        if (array_search('client_permission', $includes) !== FALSE)
            $data->clientPermission = $this->clientPermission->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
