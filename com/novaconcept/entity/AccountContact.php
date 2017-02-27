<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * AccountContact
 * @ORM\Table(name="account_contact", indexes={@ORM\Index(name="FK_account_info", columns={"FK_account_info"})})
 * @ORM\Entity
 */
class AccountContact extends AbstractEntity 
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var AccountInfo
     * @ORM\ManyToOne(targetEntity="AccountInfo", inversedBy="accountContactCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;
    
    /**
     * @var UserInfo
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="accountContactCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;

    public function getId() { return $this->id; }
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function setUserInfo($userInfo) { $this->userInfo = $userInfo; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    
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
        $data->id = $this->id;
        $data->accountInfo = $this->accountInfo->getId();
        $data->userInfo = $this->userInfo->getId();
                
        if ( array_search('account_info', $includes) !== FALSE )
            $data->accountInfo = $this->accountInfo->getData();
        
        if ( array_search('user_info', $includes) !== FALSE )
            $data->userInfo = $this->userInfo->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
