<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountContact;
use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\QuizResults;
use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\UserAuthentication;
use com\novaconcept\entity\UserPermission;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * UserInfo
 * @ORM\Table(name="user_info")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserInfo extends AbstractEntity
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=256, nullable=false)
     */
    private $username;
    
    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;
    
    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", nullable=false)
     */
    private $firstName;

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
    
    /**
     * @var UserAuthentication
     * @ORM\OneToOne(targetEntity="UserAuthentication", mappedBy="userInfo", cascade={"all"})
     */
    private $userAuthentication;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserAccount", mappedBy="userInfo", cascade={"all"}) 
     */
    private $userAccountCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserAuthorization", mappedBy="userInfo", cascade={"all"}) 
     */
    private $userAuthorizationCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="DepartmentAuthorization", mappedBy="userInfo", cascade={"all"}) 
     */
    private $departmentAuthorizationCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="QuizResults", mappedBy="userInfo", cascade={"all"}) 
     */
    private $quizResultsCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AccountContact", mappedBy="userInfo", cascade={"all"})
     **/
    private $accountContactCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="UserPermission")
     * @ORM\JoinTable(name="user_authorization",
     *      joinColumns={@ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_user_permission", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $userPermissionCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="DepartmentInfo")
     * @ORM\JoinTable(name="department_authorization",
     *      joinColumns={@ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_department_info", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $departmentInfoCollection;
    
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AccountInfo")
     * @ORM\JoinTable(name="user_account",
     *      joinColumns={@ORM\JoinColumn(name="FK_user_info", referencedColumnName="PK_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="FK_account_info", referencedColumnName="PK_id", unique=true)}
     *      )
     **/
    private $accountInfoCollection;
    
    public function __construct()
    {
        $this->userAccountCollection = new ArrayCollection();
        $this->userAuthorizationCollection = new ArrayCollection();
        $this->userPermissionCollection = new ArrayCollection();
        $this->departmentAuthorizationCollection = new ArrayCollection();
        $this->quizResultsCollection = new ArrayCollection();
        $this->departmentInfoCollection = new ArrayCollection();
        $this->accountContactCollection = new ArrayCollection();
        $this->accountInfoCollection = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setUsername($username) { $this->username = $username; }
    public function getUsername() { return $this->username; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    public function getFirstName() { return $this->firstName; }
    public function setFirstame($firstName) { $this->firstName = $firstName; }
    public function getCreatedOn() { return $this->createdOn; }
    public function getModifiedOn() { return $this->modifiedOn; }
    public function setUserAuthentication($userAuthentication) { $this->userAuthentication = $userAuthentication; return $this; }
    public function getUserAuthentication() { return $this->userAuthentication; }
    public function getUserAccountCollection() { return $this->userAccountCollection; }
    public function getUserAuthorizationCollection() { return $this->userAuthorizationCollection; }
    public function getUserPermissionCollection() { return $this->userPermissionCollection; }
    public function getDepartmentAuthorizationCollection() { return $this->departmentAuthorizationCollection; }
    public function getQuizResultsCollection() { return $this->quizResultsCollection; }
    public function getDepartmentInfoCollection() { return $this->departmentInfoCollection; }
    public function getAccountInfoCollection() { return $this->accountInfoCollection; }
    
        
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
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
        $this->username = $requestData->username;
        $this->name = $requestData->name;
        $this->firstName = $requestData->firstName;
        
        return $this;
    }
    
    public function mergePostData($requestData)
    {
        if (isset($requestData->username))
            $this->username = $requestData->username;
        
        if (isset($requestData->name))
            $this->name = $requestData->name;
        
        if (isset($requestData->firstName))
            $this->firstName = $requestData->firstName;
        
        return $this;
    }
    
    /**
     * 
     * @param array $permissions
     * @param int $accountId
     * @return boolean
     */
    public function validatePermissions($permissions, $accountId = NULL)
    {
        $counter = 0;
        $hasPermission = FALSE;
        
        foreach ($permissions->getPermissionList() as $permission)
        {
            $hasPermission = FALSE;
            $this->userAuthorizationCollection->first();
            while ($this->userAuthorizationCollection->current() != NULL)
            {
                if ($this->userAuthorizationCollection->current()->getUserPermission()->getName() == "is_god")
                    return TRUE;
                
                if ($accountId == NULL)
                {
                    if ($this->userAuthorizationCollection->current()->getUserPermission()->getName() == $permission)
                    {
                        $hasPermission = TRUE;
                        $counter += 1;
                        break;
                    }
                }
                else 
                {
                    if ($this->userAuthorizationCollection->current()->getUserPermission()->getName() == $permission 
                            && $this->userAuthorizationCollection->current()->getAccountInfo()->getId() == $accountId)
                    {
                        $hasPermission = TRUE;
                        $counter += 1;
                        break;
                    }
                }
                $this->userAuthorizationCollection->next();
            }
            if ($hasPermission === FALSE)
                return FALSE;
        }
        if ($counter == count($permissions->getPermissionList()))
            return TRUE;
        
        return FALSE;
    }
    
    /**
     * 
     * @param AccountInfo $account
     * @return boolean
     */
    public function validateAccount($account)
    {
        if (is_int($this->accountInfoCollection->indexOf($account)))
            return TRUE;
        
        return FALSE;
    }
    
    /**
     * 
     * @param UserPermission $permission
     * @param AccountInfo $accountId
     * @return boolean
     */
    public function hasPermission($permission, $accountId = NULL)
    {
        $this->userAuthorizationCollection->first();
        while ( $this->userAuthorizationCollection->current() != NULL )
        {
            if ($this->userAuthorizationCollection->current()->getUserPermission()->getName() == "is_god")
                    return TRUE;
            
            if ($accountId === NULL)
            {
                if ($this->userAuthorizationCollection->current()->getUserPermission() == $permission)
                    return TRUE;
            }else{
                if ($this->userAuthorizationCollection->current()->getUserPermission() == $permission 
                            && $this->userAuthorizationCollection->current()->getAccountInfo()->getId() == $accountId)
                    return TRUE;
            }
            $this->userAuthorizationCollection->next();
        }
        
        return FALSE;
    }
    
    /**
     * @param array $includes
     * @return stdClass
     */
    public function getData($includes = NULL)
    {        
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->username = $this->getUsername();
        $data->name = $this->getName();
        $data->firstName = $this->getFirstName();
        $data->modifiedOn = $this->getModifiedOn()->getTimestamp();
        $data->createdOn = $this->getCreatedOn()->getTimestamp();
        
        if (array_search('user_account', $includes) !== FALSE)
        {
            $account = array();
            $this->userAccountCollection->first();
            while ($this->userAccountCollection->current() != NULL)
            {
                array_push($account, $this->userAccountCollection->current()->getData());
                $this->userAccountCollection->next();
            }
            $data->userAccount = $account;
        }
        
        if (array_search('account_info', $includes) !== FALSE)
        {
            $accountInfo = array();
            $this->accountInfoCollection->first();
            while ($this->accountInfoCollection->current() != NULL)
            {
                array_push($accountInfo, $this->accountInfoCollection->current()->getData());
                $this->accountInfoCollection->next();
            }
            $data->accountInfo = $accountInfo;
        }
                
        if (array_search('department_info', $includes) !== FALSE)
        {
            $department = array();
            $this->departmentInfoCollection->first();
            while ($this->departmentInfoCollection->current() != NULL)
            {
                array_push($department, $this->departmentInfoCollection->current()->getData());
                $this->departmentInfoCollection->next();
            }
            $data->departmentInfo = $department;
        }

        if (array_search('user_permission', $includes) !== FALSE)
        {
            $permission = array();
            $this->userPermissionCollection->first();
            while ($this->userPermissionCollection->current() != NULL)
            {
                array_push( $permission, $this->userPermissionCollection->current()->getData(NULL) );
                $this->userPermissionCollection->next();
            }
            $data->userPermission = $permission;
        }
        
        if (array_search('user_authorization', $includes) !== FALSE)
        {
            $authorization = array();
            $this->userAuthorizationCollection->first();
            while ($this->userAuthorizationCollection->current() != NULL)
            {
                array_push($authorization, $this->userAuthorizationCollection->current()->getData());
                $this->userAuthorizationCollection->next();
            }
            $data->userAuthorization = $authorization;
        }
        
        if (array_search('quiz_result', $includes) !== FALSE)
        {
            $quizResult = array();
            $this->quizResultsCollection->first();
            while ($this->quizResultsCollection->current() != NULL)
            {
                array_push($authorization, $this->quizResultsCollection->current()->getData());
                $this->quizResultsCollection->next();
            }
            $data->quizResults = $quizResult;
        }
        
        if (array_search('user_authorization_permission', $includes) !== FALSE)
        {
            $authorizationPermission = array();
            $this->userAuthorizationCollection->first();
            while ($this->userAuthorizationCollection->current() != NULL)
            {
                $userAuthorization = $this->userAuthorizationCollection->current()->getData();
                $userAuthorization->permissionName = $this->userAuthorizationCollection->current()->getUserPermission()->getName();
                $userAuthorization->permissionIsActive = $this->userAuthorizationCollection->current()->getUserPermission()->getIsActive();
                array_push($authorizationPermission, $userAuthorization);
                $this->userAuthorizationCollection->next();
            }
            $data->userAuthorizationPermission = $authorizationPermission;
        }
        
        if (array_search('department_authorization', $includes) !== FALSE)
        {
            $authorizationDepartment = array();
            $this->departmentAuthorizationCollection->first();
            while ($this->departmentAuthorizationCollection->current() != NULL)
            {
                array_push( $authorizationDepartment, $this->departmentAuthorizationCollection->current()->getData() );
                $this->departmentAuthorizationCollection->next();
            }
            $data->departmentAuthorization = $authorizationDepartment;
        }

        if (array_search('recursive_children', $includes) !== FALSE)
        {
            $authorizationDepartment = array();
            $this->departmentAuthorizationCollection->first();
            while ($this->departmentAuthorizationCollection->current() != NULL)
            {
                if ($this->departmentAuthorizationCollection->current()->getIsRecursive() === TRUE)
                {
                    array_push($authorizationDepartment, $this->departmentAuthorizationCollection->current()->getDepartmentInfo()->getData(array('recursive_children')));
                }
                else 
                {
                    array_push($authorizationDepartment, $this->departmentAuthorizationCollection->current()->getDepartmentInfo()->getData());
                }
                $this->departmentAuthorizationCollection->next();
            }
            $data->departmentAuthorization = $authorizationDepartment;
        }

        if (array_search('user_authentication', $includes) !== FALSE)
            $data->userAuthentication = $this->userAuthentication->getData();
        
        return $data;
    }
    
    
    public function getDataArray()
    {        
        $data = [];
        $data[] = $this->getId();
        $data[] = $this->getUsername();
        $data[] = $this->getName();
        $data[] = $this->getFirstName();
        $data[] = $this->getCreatedOn()->getTimestamp();
        $data[] = $this->getModifiedOn()->getTimestamp();
        
        return $data;
    }
    /**
     * @return string
     */
    public function __toString()
    {
       return strval($this->id);
    }
}