<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\transient\Authorization;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\utility\PasswordHash;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * UserAuthentication
 * @ORM\Table(name="user_authentication", indexes={@ORM\Index(name="FPK_user_info", columns={"FPK_user_info"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserAuthentication extends AbstractEntity
{
    private $CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    /**
     * @var UserInfo
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="UserInfo", inversedBy="userAuthentication")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FPK_user_info", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;
    
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
     * @ORM\Column(name="private_key", type="string", length=34, nullable=true)
     */
    private $privateKey;
    
    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=34, nullable=false)
     */
    private $password;
    
    /**
     * @var integer
     * @ORM\Column(name="attempt_fail", type="integer", nullable=false)
     */
    private $attemptFail;
    
    /**
     * @var boolean
     * @ORM\Column(name="force_change", type="boolean", nullable=false)
     */
    private $forceChange;

    /**
     * @var \DateTime
     * @ORM\Column(name="changed_on", type="datetime", nullable=false)
     */
    private $changedOn;
    
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
    
    public function setUserInfo($userInfo) { $this->userInfo = $userInfo; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setPublicKey($publicKey) { $this->publicKey = $publicKey; return $this; }
    public function getPublicKey() { return $this->publicKey; }
    public function getPrivateKey() { return $this->privateKey; }
    public function getPassword() { return $this->password; }
    public function setAttemptFail($attemptFail) { $this->attemptFail = $attemptFail; return $this; }
    public function getAttemptFail() { return $this->attemptFail; }
    public function setForceChange($forceChange) { $this->forceChange = $forceChange; return $this; }
    public function getForceChange() { return $this->forceChange; }
    public function setChangedOn($changedOn) { $this->changedOn = $changedOn; return $this; }
    public function getChangedOn() { return $this->changedOn; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getModifiedOn() { return $this->modifiedOn; }
    
    public function generatePrivateKey() 
    {   
        $privateKeyHash = new PasswordHash(8, TRUE);
        $randomString = '';
        for ($i = 0; $i < 32; $i++)
        {
            $randomString .= $this->CHARACTERS[rand(0, strlen($this->CHARACTERS) - 1)];
        }
        $randomString = md5($randomString);
        $this->privateKey = $privateKeyHash->HashPassword($randomString); 
        return $randomString;
    }
    
    public function setPassword($password) 
    { 
        $passwordHash = new PasswordHash(8, TRUE);
        $this->password = $passwordHash->HashPassword($password); 
        $this->forceChange = FALSE;
        return $this; 
    }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->isActive = TRUE;
        $this->attemptFail = 0;
        if (!isset($this->forceChange))
            $this->forceChange = FALSE;
        
        $randomString = '';
        for ($i = 0; $i < 32; $i++)
        {
            $randomString .= $this->CHARACTERS[rand(0, strlen($this->CHARACTERS) - 1)];
        }
        $this->publicKey = md5($randomString);

        $this->createdOn = new DateTime();
        $this->modifiedOn = new DateTime();
        $this->changedOn = new DateTime();
    }
    
    /** @ORM\PreUpdate */
    public function onPreUpdate()
    {
        $this->modifiedOn = new DateTime();
    }
    
    public function mapPostData($requestData) 
    {
        $passwordHash = new PasswordHash(8, TRUE);
        $this->password = $passwordHash->HashPassword($requestData->password);
        if (isset($requestData->forceChange))
            $this->forceChange = $requestData->forceChange;
        
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if (isset($requestData->isActive))
            $this->setIsActive($requestData->isActive);
        
        if (isset($requestData->forceChange))
            $this->setForceChange ($requestData->forceChange);

        if (isset($requestData->password))
        {
            $passwordHash = new PasswordHash(8, TRUE);
            $this->password = $passwordHash->HashPassword($requestData->password);
            $this->changedOn = new DateTime();
        }
        
        if (isset($requestData->attemptFail))
            $this->attemptFail = $requestData->attemptFail;
        
        return $this;
    }
    
    public function addAttemptFail()
    {
        $this->attemptFail += 1;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        
        $data = new stdClass();
        $data->id = $this->getUserInfo()->getId();
        $data->isActive = $this->getIsActive();
        $data->attemptFail = $this->getAttemptFail();
        $data->forceChange = $this->getForceChange();
        $data->changedOn = $this->getChangedOn()->getTimestamp();
        $data->modifiedOn = $this->getModifiedOn()->getTimestamp();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('user_info', $includes) !== FALSE)
            $data->userInfo = $this->userInfo->getData();
        
        return $data;
    }
    
    /**
     * @param Authorization $authorization
     * @return boolean
     */
    public function validateAuthorization($authorization)
    {
        $privateKeyHash = new PasswordHash(8, TRUE);
        if ($privateKeyHash->CheckPassword($authorization->getUserPrivate(), $this->privateKey))
            return TRUE;
        
        return FALSE;
    }
    
    public function __toString()
    {
       return strval($this->getUserInfo()->getId());
    }
}