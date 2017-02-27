<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountContact;
use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\DepartmentAuthorization;
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
 * CreateUsers
 * @ORM\Table(name="create_users")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CreateUsers extends AbstractEntity
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=128, nullable=false)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="prenom", nullable=false)
     */
    private $prenom;
    
    /**
     * @var string
     * @ORM\Column(name="nom", nullable=false)
     */
    private $nom;
    
    /**
     * @var string
     * @ORM\Column(name="psw", nullable=false)
     */
    private $psw;
    
    /**
     * @var string
     * @ORM\Column(name="groupe", nullable=false)
     */
    private $groupe;
    
    /**
     * @var string
     * @ORM\Column(name="agence", nullable=false)
     */
    private $agence;
    
    /**
     * @var string
     * @ORM\Column(name="role", nullable=false)
     */
    private $role;

    
    public function __construct()
    {
        
    }

    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFirstName() { return $this->prenom; }
    public function getName() { return $this->nom; }
    public function getPsw() { return $this->psw; }
    public function getGroupe() { return $this->groupe; }
    public function getAgence() { return $this->agence; }
    public function getRole() { return $this->role; }
        
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        
    }
    
    /** @ORM\PreUpdate */
    public function onPreUpdate()
    {
        
    }
    
    public function mapPostData($requestData) 
    {

    }
    
    public function mergePostData($requestData)
    {

    }

    public function getData($includes = NULL)
    {        
        
    }
}