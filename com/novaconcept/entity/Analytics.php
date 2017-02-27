<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AnalyticsTag;
use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Analytics
 * @ORM\Table(name="analytics", indexes={@ORM\Index(name="FK_analytics_tag", columns={"FK_analytics_tag"}), @ORM\Index(name="FK_client_info", columns={"FK_client_info"}), @ORM\Index(name="FK_user_info", columns={"FK_user_info"})})
 * @ORM\Entity
 */
class Analytics extends AbstractEntity 
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="RFK_content_info", type="integer", nullable=true)
     */
    private $contentInfo;

    /**
     * @var string
     * @ORM\Column(name="session_code", type="string", length=32, nullable=false)
     */
    private $sessionCode;

    /**
     * @var string
     * @ORM\Column(name="install_code", type="string", length=32, nullable=false)
     */
    private $installCode;
    
    /**
     * @var string
     * @ORM\Column(name="page_code", type="string", length=32, nullable=false)
     */
    private $pageCode;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    /**
     * @var ClientInfo
     * @ORM\ManyToOne(targetEntity="ClientInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_client_info", referencedColumnName="PK_id", nullable = false)
     * })
     */
    private $clientInfo;
    
    /**
     * @var string
     * @ORM\Column(name="json_data", type="string", nullable=true)
     */
    private $jsonData;

    /**
     * @var AnalyticsTag
     *
     * @ORM\ManyToOne(targetEntity="AnalyticsTag", cascade={"persist", "merge"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_analytics_tag", referencedColumnName="PK_id")
     * })
     */
    private $analyticsTag;

    /**
     * @var UserInfo
     *
     * @ORM\ManyToOne(targetEntity="UserInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;

    public function getId() { return $this->id; }
    public function getContentInfo() { return $this->contentInfo; }
    public function getSessionCode() { return $this->sessionCode; }
    public function getInstallCode() { return $this->deviceCode; }
    public function getPageCode() { return $this->pageCode; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getClientInfo() {return $this->clientInfo; }
    public function setClientInfo($clientInfo) { $this->clientInfo = $clientInfo; return $this; }
    public function getAnalyticsTag() { return $this->analyticsTag; }
    public function setAnalyticsTag($tag) { $this->analyticsTag = $tag; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    public function setUserInfo($userInfo) { $this->userInfo = $userInfo; return $this; }
    public function getJsonData() { return $this->jsonData; }
    public function setJsonData($jsonData) { $this->jsonData = $jsonData; return $this; }
    
    public function mapPostData($requestData)
    {
        $this->contentInfo = $requestData->contentInfo;
        $this->installCode = $requestData->installCode;
        $this->sessionCode = $requestData->sessionCode;
        $this->pageCode = $requestData->pageCode;
        if (isset($requestData->jsonData) && $requestData->jsonData != '')
        {
            $this->jsonData = $requestData->jsonData;
        }
        else
        {
            $this->jsonData = NULL;
        }
        $this->createdOn = new DateTime();
        $this->createdOn->setTimestamp($requestData->createdOn);
        
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
        
        $data->contentInfo = $this->contentInfo;
        $data->installCode = $this->installCode;
        $data->sessionCode = $this->sessionCode;
        $data->pageCode = $this->pageCode;
        $data->jsonData = $this->jsonData;
        $data->createdOn = $this->createdOn->getTimestamp();
        
        if (array_search('analytics_tag', $includes) !== FALSE)
            $data->analyticsTag = $this->analyticsTag->getData();
        
        if (array_search('client_info', $includes) !== FALSE)
            $data->clientInfo = $this->clientInfo->getData();
        
        if (array_search('user_info', $includes) !== FALSE)
            $data->userInfo = $this->userInfo->getData();
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
