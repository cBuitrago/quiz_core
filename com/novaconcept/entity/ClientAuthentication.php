<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\transient\Authorization;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * ClientAuthentication
 * @ORM\Table(name="client_authentication", indexes={@ORM\Index(name="private_key", columns={"private_key"}), @ORM\Index(name="FPK_client_info", columns={"FPK_client_info"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ClientAuthentication extends AbstractEntity
{
    private $CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    /**
     * @var ClientInfo
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="ClientInfo", inversedBy="clientAuthentication")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FPK_client_info", referencedColumnName="PK_id")
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
     * @ORM\Column(name="public_key", type="string", length=32, nullable=false)
     */
    private $publicKey;

    /**
     * @var string
     * @ORM\Column(name="private_key", type="string", length=32, nullable=false)
     */
    private $privateKey;
    
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
    
    public function setClientInfo($clientInfo) { $this->clientInfo = $clientInfo; return $this; }
    public function getClientInfo() { return $this->clientInfo; }  
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setPublicKey($publicKey) { $this->publicKey = $publicKey; return $this; }
    public function getPublicKey() { return $this->publicKey; }
    public function setPrivateKey($privateKey) { $this->privateKey = $privateKey; return $this; }
    public function getPrivateKey() { return $this->privateKey; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getModifiedOn() { return $this->modifiedOn; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->setIsActive(TRUE);
        $this->createdOn = new DateTime();
        $this->modifiedOn = new DateTime();
    }
    
    /** @ORM\PreUpdate */
    public function onPreUpdate()
    {
        $this->modifiedOn = new DateTime();
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
        if ($includes === NULL)
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->getClientInfo()->getId();
        $data->isActive = $this->getIsActive();
        $data->publicKey = $this->getPublicKey();
        $data->privateKey = $this->getPrivateKey();
        $data->modifiedOn = $this->getModifiedOn()->getTimestamp();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('client_info', $includes) !== FALSE)
            $data->clientInfo = $this->clientInfo->getData();
        
        return $data;
    }
    
    public function generateKeys()
    {
        $randomStringPublic = '';
        $randomStringPrivate = '';
        for ($i = 0; $i < 32; $i++)
        {
            $randomStringPublic .= $this->CHARACTERS[rand(0, strlen($this->CHARACTERS) - 1)];
            $randomStringPrivate .= $this->CHARACTERS[rand(0, strlen($this->CHARACTERS) - 1)];
        }
        $randomStringPublic = md5($randomStringPublic);
        $randomStringPrivate = md5($randomStringPrivate);
        $this->setPublicKey($randomStringPublic);
        $this->setPrivateKey($randomStringPrivate);
    }
    
    /**
     * @param Authorization $authorization
     * @return boolean
     */
    public function validateAuthorization($authorization)
    {
        return $authorization->validateSignature($this->privateKey);
    }

    public function __toString()
    {
       return strval($this->clientInfo->getId());
    }
}
