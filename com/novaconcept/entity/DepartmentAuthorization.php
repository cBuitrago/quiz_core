<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * DepartmentAuthorization
 * @ORM\Table(name="department_authorization", indexes={@ORM\Index(name="FK_department_info", columns={"FK_department_info"}), @ORM\Index(name="FK_user_info", columns={"FK_user_info"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class DepartmentAuthorization extends AbstractEntity
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
     * @ORM\Column(name="is_recursive", type="boolean", nullable=false)
     */
    private $isRecursive;

    /**
     * @var DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    /**
     * @var DepartmentInfo
     * @ORM\ManyToOne(targetEntity="DepartmentInfo", inversedBy="departmentAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_department_info", referencedColumnName="PK_id")
     * })
     */
    private $departmentInfo;

    /**
     * @var UserInfo
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="departmentAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;

    public function getId() { return $this->id; }
    public function setIsRecursive($isRecursive) { $this->isRecursive = $isRecursive; return $this; }
    public function getIsRecursive() { return $this->isRecursive; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function setDepartmentInfo($departmentInfo) { $this->departmentInfo = $departmentInfo; return $this; }
    public function getDepartmentInfo() { return $this->departmentInfo; }
    public function setUserInfo($userInfo = null) { $this->userInfo = $userInfo; return $this; }
    public function getUserInfo() { return $this->userInfo; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->isRecursive = $requestData->isRecursive;
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if (isset($requestData->isRecursive) === TRUE)
            $this->isRecursive = $requestData->isRecursive;
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->userId = $this->getUserInfo()->getId();
        $data->departmentId = $this->getDepartmentInfo()->getId();
        $data->isRecursive = $this->getIsRecursive();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('department_info', $includes) !== FALSE)
            $data->departmentInfo = $this->getDepartmentInfo()->getData();
        
        if (array_search('user_info', $includes) !== FALSE)
            $data->userInfoId = $this->getUserInfo()->getData();
                    
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
