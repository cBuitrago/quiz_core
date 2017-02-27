<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\UserInfo;
use com\novaconcept\entity\UserPermission;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * UserAuthorization
 * @ORM\Table(name="user_authorization", indexes={@ORM\Index(name="FK_user_info", columns={"FK_user_info"}), @ORM\Index(name="FK_user_permission", columns={"FK_user_permission"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserAuthorization extends AbstractEntity 
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
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="userAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;
    
    /**
     * @var AccountInfo
     * @ORM\ManyToOne(targetEntity="AccountInfo", inversedBy="userAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;

    /**
     * @var UserPermission
     * @ORM\ManyToOne(targetEntity="UserPermission", inversedBy="userAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user_permission", referencedColumnName="PK_id")
     * })
     */
    private $userPermission;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    public function getId() { return $this->id; }
    public function setUserInfo($userInfo) { $this->userInfo = $userInfo; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function setUserPermission($userPermission) { $this->userPermission = $userPermission; return $this; }
    public function getUserPermission() { return $this->userPermission; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    
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
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        $data->userInfo = $this->getUserInfo()->getId();
        $data->accountInfo = $this->getAccountInfo()->getId();
        $data->userPermission = $this->getUserPermission()->getId();
        
        if ( array_search('user_info', $includes) !== FALSE )
            $data->userInfo = $this->getUserInfo()->getData();
        
        if ( array_search('account_info', $includes) !== FALSE )
            $data->accountInfo = $this->getAccountInfo()->getData();
        
        if ( array_search('user_permission', $includes) !== FALSE )
            $data->userPermission = $this->getUserPermission()->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
