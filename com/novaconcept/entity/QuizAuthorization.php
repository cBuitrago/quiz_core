<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\UserInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * QuizAuthorization
 * @ORM\Table(name="quiz_authorization", indexes={@ORM\Index(name="FK_department_info", columns={"FK_department_info"}), @ORM\Index(name="QUIZ_ID", columns={"QUIZ_ID"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class QuizAuthorization extends AbstractEntity
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var DateTime
     * @ORM\Column(name="START_DATE", type="datetime", nullable=false)
     */
    private $startDate;
    
    /**
     * @var DateTime
     * @ORM\Column(name="END_DATE", type="datetime", nullable=false)
     */
    private $endDate;

    /**
     * @var DepartmentInfo
     * @ORM\ManyToOne(targetEntity="DepartmentInfo", inversedBy="departmentAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FK_department_info", referencedColumnName="PK_id")
     * })
     */
    private $departmentInfo;

    /**
     * @var Quiz
     * @ORM\ManyToOne(targetEntity="Quiz", inversedBy="quizAuthorizationCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="QUIZ_ID", referencedColumnName="ID")
     * })
     */
    private $quiz;

    public function getId() { return $this->id; }
    public function setStartDate($startDate) { $this->startDate = $startDate; return $this; }
    public function getStartDate() { return $this->startDate; }
    public function setEndDate($endDate) { $this->endDate = $endDate; return $this; }
    public function getEndDate() { return $this->endDate; }
    public function setCreatedOn($createdOn) { $this->createdOn = $createdOn; return $this; }
    public function getCreatedOn() { return $this->createdOn; }
    public function setDepartmentInfo($departmentInfo) { $this->departmentInfo = $departmentInfo; return $this; }
    public function getDepartmentInfo() { return $this->departmentInfo; }
    public function setQuizInfo($quiz) { $this->quiz = $quiz; return $this; }
    public function getQuizInfo() { return $this->quiz; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        
    }
    
    public function mapPostData($requestData)
    {
        $this->setStartDate($requestData->startDate); 
        $this->setEndDate($requestData->endDate);
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if ( isset($requestData->startDate) )
            $this->setStartDate($requestData->startDate);
        
        if ( isset($requestData->endDate) )
            $this->setEndDate($requestData->endDate);
        
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->startDate = $this->getStartDate();
        $data->endDate = $this->getEndDate();
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
