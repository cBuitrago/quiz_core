<?php

namespace com\novaconcept\entity;

use com\novaconcept\utility\ApiConfig;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * QuizGroupQuizList
 *
 * @ORM\Table(name="quiz_group_quiz_list")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class QuizGroupQuizList extends AbstractEntity 
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
     * @ORM\Column(name="QUIZ_ID", type="integer", nullable=false)
     */
    private $quizId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="ORDER_NB", type="integer", nullable=false)
     */
    private $orderNB;
    
    public function __construct()
    {
        
    }

    public function getId() { return $this->id; }
    public function setQuizGroupID($quizGroupId) { $this->quizGroupId = $quizGroupId; return $this; }
    public function getQuizGroupID() { return $this->quizGroupId; }
    public function setQuizID($quizId) { $this->quizId = $quizId; return $this; }
    public function getQuizID() { return $this->quizId; }
    public function setOrderNB($orderNB) { $this->orderNB = $orderNB; return $this; }
    public function getOrderNB() { return $this->orderNB; }
   
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
        $this->setQuizID($requestData->quizId);
        $this->setQuizGroupID($requestData->quizGroupId); 
        $this->setOrderNB($requestData->orderNB);
    }
    
    public function mergePostData($requestData)
    {
        if ( isset($requestData->quizId) )
            $this->setQuizID($requestData->quizId);
        
        if ( isset($requestData->quizGroupId) )
            $this->setQuizGroupID($requestData->quizGroupId);
        
        if ( isset($requestData->orderNB) )
            $this->setOrderNB($requestData->orderNB);
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
        {
            $includes = array();
        }
        
        $data = new stdClass();
        
        $data->id = $this->getId();
        $data->quizId = $this->getQuizID();
        $data->quizGroupId = $this->getQuizGroupID();
        $data->orderNB = $this->getOrderNB();
        
        return $data;
    }
}
