<?php

namespace com\novaconcept\entity;

use com\novaconcept\utility\ApiConfig;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * UserQuizGroup
 *
 * @ORM\Table(name="user_quiz_group")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserQuizGroup extends AbstractEntity 
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="QUIZ_GROUP_ID", type="integer", nullable=false)
     */
    private $quizGroupId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="USER_ID", type="integer", nullable=false)
     */
    private $userId;

    public function __construct()
    {
        
    }

    public function getId() { return $this->id; }
    public function setQuizGroupID($quizGroupId) { $this->quizGroupId = $quizGroupId; return $this; }
    public function getQuizGroupID() { return $this->quizGroupId; }
    public function setUserId($userId) { $this->userId = $userId; return $this; }
    public function getUserId() { return $this->userId; }
    
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
        $this->setQuizGroupID($requestData->quizGroupId);
        $this->setUserId($requestData->userId); 
    }
    
    public function mergePostData($requestData)
    {
        if ( isset($requestData->quizGroupId) )
            $this->setQuizGroupID($requestData->quizGroupId);
        
        if ( isset($requestData->userId) )
            $this->setUserId($requestData->userId);
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
        {
            $includes = array();
        }
        
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->quizGroupId = $this->getQuizGroupID();
        $data->userId = $this->getUserId();
        
        return $data;
    }
}
