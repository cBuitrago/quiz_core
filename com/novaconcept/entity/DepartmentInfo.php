<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * DepartmentInfo
 * @ORM\Table(name="department_info", indexes={@ORM\Index(name="FK_account_info", columns={"FK_account_info"}), @ORM\Index(name="FK_parent", columns={"FK_parent"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class DepartmentInfo extends AbstractEntity 
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
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=512, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    /**
     * @var AccountInfo
     * @ORM\ManyToOne(targetEntity="AccountInfo", inversedBy="departmentInfoCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="DepartmentAuthorization", mappedBy="departmentInfo", cascade={"all"})
     **/
    private $departmentAuthorizationCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="QuizAuthorization", mappedBy="departmentInfo", cascade={"all"})
     **/
    private $quizAuthorizationCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="DepartmentInfo", mappedBy="parent")
     **/
    private $childrenCollection;
    
    /**
     * @var DepartmentInfo
     *
     * @ORM\ManyToOne(targetEntity="DepartmentInfo", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_parent", referencedColumnName="PK_id")
     * })
     */
    private $parent;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="UserInfo")
     * @ORM\JoinTable(name="department_authorization",
     *      joinColumns={@ORM\JoinColumn(name="FK_department_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $userInfoCollection;
    
    public function __construct() 
    {
        $this->childrenCollection = new ArrayCollection();
        $this->departmentAuthorizationCollection = new ArrayCollection();
        $this->userInfoCollection = new ArrayCollection();
        $this->quizAuthorizationCollection = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setIsActive($isActive) { $this->isActive = $isActive; return $this; }
    public function getIsActive() { return $this->isActive; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getName() { return $this->name; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getDescription() { return $this->description; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function setParent($parent = NULL) { $this->parent = $parent; return $this; }
    public function getParent() { return $this->parent; }
    public function getChildrenCollection() { return $this->childrenCollection; }
    public function getDepartmentAuthorizationCollection() { return $this->departmentAuthorizationCollection; }
    public function getQuizAuthorizationCollection() { return $this->quizAuthorizationCollection; }
    public function getUserInfoCollection() { return $this->userInfoCollection; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->isActive = TRUE;
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->name = $requestData->name;
        $this->description = $requestData->description;
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if (isset($requestData->name))
            $this->name = $requestData->name;
        
        if (isset($requestData->description))
            $this->description = $requestData->description;
        
        if (isset($requestData->isActive))
            $this->isActive = $requestData->isActive;
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->id;
        $data->name = $this->name;
        $data->description = $this->description;
        $data->isActive = $this->isActive;
        $data->createdOn = $this->createdOn->getTimestamp();
        $data->parent = ($this->parent != NULL)? $this->getParent()->getId() : NULL;
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->accountInfo->getData();
                
        if (array_search('children', $includes) !== FALSE)
        {
            $child = array();
            $this->childrenCollection->first();
            while( $this->childrenCollection->current() != NULL )
            {
                array_push( $child, $this->childrenCollection->current()->getData() );
                $this->childrenCollection->next();
            }
            $data->childrenCollection = $child;
        }

        if (array_search('recursive_children', $includes) !== FALSE)
        {
            $child = array();
            $this->childrenCollection->first();
            while( $this->childrenCollection->current() != NULL )
            {
                array_push( $child, $this->childrenCollection->current()->getData(array('recursive_children')) );
                $this->childrenCollection->next();
            }
            $data->recursiveChildrenCollection = $child;
        }
        
        if (array_search('user_info', $includes) !== FALSE)
        {
            $user = array();
            $this->userInfoCollection->first();
            while( $this->userInfoCollection->current() != NULL )
            {
                array_push( $user, $this->userInfoCollection->current()->getData() );
                $this->userInfoCollection->next();
            }
            $data->users = $user;
        }
        
        if (array_search('authorizations', $includes) !== FALSE)
        {
            $authorization = array();
            $this->departmentAuthorizationCollection->first();
            while( $this->departmentAuthorizationCollection->current() != NULL )
            {
                array_push( $authorization, $this->departmentAuthorizationCollection->current()->getData() );
                $this->departmentAuthorizationCollection->next();
            }
            if ( !empty($authorization) )
            {
                $data->authorizations = $authorization;
            }
        }
        
        if (array_search('parent', $includes) !== FALSE)
        {
            $data->parent = NULL;
            if ($this->parent != NULL)
                $data->parent = $this->parent->getData();
        }

        if (array_search('parents', $includes) !== FALSE)
        {
            if ( $this->getParent() != NULL )
            {
                $parents = array('parents');
                $data->parents = $this->getParent()->getData($parents);
            }
        }
        
        return $data;
    }
    
    /**
     * @todo authenticating a user to a department taking in consideration recusion
     * @param type $userInfo
     * @return boolean
     */
    public function authenticateUser($userInfo)
    {
        return FALSE;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
