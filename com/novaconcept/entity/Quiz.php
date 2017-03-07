<?php

namespace com\novaconcept\entity;

use com\novaconcept\utility\ApiConfig;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * Quiz
 *
 * @ORM\Table(name="quiz")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Quiz extends AbstractEntity 
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
     * @var string
     *
     * @ORM\Column(name="QUIZ_ID", type="string", nullable=false)
     */
    private $quizId;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="LOCKED_ON_COMPLETION", type="integer", nullable=false)
     */
    private $lockedOnCompletion;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="TIME_TO_COMPLETE", type="integer", nullable=false)
     */
    private $timeToComplete;
    
    /**
     * @var string
     *
     * @ORM\Column(name="QUIZ_DATA", type="string", nullable=false)
     */
    private $quizData;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="IS_USER_CAN_DISPLAY_CHART", type="integer", nullable=false)
     */
    private $isUserCanDisplayChart;
        
    /**
     * @var integer
     *
     * @ORM\Column(name="IS_USER_CAN_DISPLAY_QA", type="integer", nullable=false)
     */
    private $isUserCanDisplayQa;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="IS_ENABLED", type="integer", nullable=false)
     */
    private $isEnabled;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="IS_USER_SEE_GOOD_ANSWER", type="integer", nullable=false)
     */
    private $isUserSeeGoodAnswer;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ANSWER_JSON", type="string", nullable=false)
     */
    private $answerJson;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="QuizAuthorization", mappedBy="quiz", cascade={"all"}) 
     */
    private $quizAuthorizationCollection;
    
    /** 
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="QuizResults", mappedBy="userInfo", cascade={"all"}) 
     */
    private $quizResultsCollection;

    public function __construct()
    {
        $this->quizAuthorizationCollection = new ArrayCollection();
        $this->quizResultsCollection = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setQuizID($quizId) { $this->quizId = $quizId; return $this; }
    public function getQuizID() { return $this->quizId; }
    public function setLockedOnCompletion($lockedOnCompletion) { $this->lockedOnCompletion = $lockedOnCompletion; return $this; }
    public function getLockedOnCompletion() { return $this->lockedOnCompletion; }
    public function setTimeToComplete($timeToComplete) { $this->timeToComplete = $timeToComplete; return $this; }
    public function getTimeToComplete() { return $this->timeToComplete; }
    public function setQuizData($quizData) { $this->quizData = $quizData; return $this; }
    public function getQuizData() { return $this->quizData; }
    public function setIsUserCanDisplayChart($isUserCanDisplayChart) { $this->isUserCanDisplayChart = $isUserCanDisplayChart; return $this; }
    public function getIsUserCanDisplayChart() { return $this->isUserCanDisplayChart; }
    public function setIsUserCanDisplayQa($isUserCanDisplayQa) { $this->isUserCanDisplayQa = $isUserCanDisplayQa; return $this; }
    public function getIsUserCanDisplayQa() { return $this->isUserCanDisplayQa; }
    public function setIsEnabled($isEnabled) { $this->isEnabled = $isEnabled; return $this; }
    public function getIsEnabled() { return $this->isEnabled; }
    public function setIsUserSeeGoodAnswer($isUserSeeGoodAnswer) { $this->isUserSeeGoodAnswer = $isUserSeeGoodAnswer; return $this; }
    public function getIsUserSeeGoodAnswer() { return $this->isUserSeeGoodAnswer; }
    public function setAnswerJson($answerJson) { $this->answerJson = $answerJson; return $this; }
    public function getAnswerJson() { return $this->answerJson; }
    public function getQuizAuthorizationCollection() { return $this->quizAuthorizationCollection; }
    public function getQuizResultsCollection() { return $this->quizResultsCollection; }


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
        $this->setLockedOnCompletion($requestData->lockedOnCompletion);
        $this->setTimeToComplete($requestData->timeToComplete); 
        $this->setQuizData($requestData->quizData); 
        $this->setIsUserCanDisplayChart($requestData->isUserCanDisplayChart); 
        $this->setIsUserCanDisplayQa($requestData->isUserCanDisplayQa); 
        $this->setIsEnabled($requestData->isEnabled);
        $this->setIsUserSeeGoodAnswer($requestData->isUserSeeGoodAnswer);
        $this->setAnswerJson($requestData->answerJson);
    }
    
    public function mergePostData($requestData)
    {
        if ( isset($requestData->QUIZ_ID) )
            $this->setQuizID($requestData->QUIZ_ID);
        
        if ( isset($requestData->LOCKED_ON_COMPLETION) ){
            $this->setLockedOnCompletion($requestData->LOCKED_ON_COMPLETION);
        }else{
            $this->setLockedOnCompletion(FALSE);
        }
        
        if ( isset($requestData->TIME_TO_COMPLETE) )
            $this->setTimeToComplete($requestData->TIME_TO_COMPLETE);
        
        if ( isset($requestData->QUIZ_DATA) )
            $this->setQuizData($requestData->QUIZ_DATA);
        
        if ( isset($requestData->IS_USER_CAN_DISPLAY_CHART) ){
            $this->setIsUserCanDisplayChart($requestData->IS_USER_CAN_DISPLAY_CHART);
        }else{
            $this->setIsUserCanDisplayChart(FALSE);
        }
        
        if ( isset($requestData->IS_USER_CAN_DISPLAY_QA) ){
            $this->setIsUserCanDisplayQa($requestData->IS_USER_CAN_DISPLAY_QA);
        }else{
            $this->setIsUserCanDisplayQa(FALSE);
        }
        
        if ( isset($requestData->IS_ENABLED) ){
            $this->setIsEnabled($requestData->IS_ENABLED);
        }else{
            $this->setIsEnabled(FALSE);
        }
        
        if ( isset($requestData->IS_USER_SEE_GOOD_ANSWER) ){
            $this->setIsUserSeeGoodAnswer($requestData->IS_USER_SEE_GOOD_ANSWER);
        }else{
            $this->setIsUserSeeGoodAnswer(FALSE);
        }
        
        if ( isset($requestData->ANSWER_JSON) )
            $this->setAnswerJson($requestData->ANSWER_JSON);
        
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
        {
            $includes = array();
        }
        
        $data = [];
        
        $data[] = $this->getId();
        $data[] = $this->getQuizID();
        $data[] = "star_date";
        $data[] = "end_date";
        $data[] = $this->getLockedOnCompletion();
        $data[] = $this->getTimeToComplete();
        $data[] = $this->getQuizData();
        $data[] = $this->getIsUserCanDisplayChart();
        $data[] = $this->getIsUserCanDisplayQa();
        $data[] = $this->getIsEnabled();
        $data[] = $this->getIsUserSeeGoodAnswer();
        $data[] = $this->getAnswerJson();
        
        return $data;
    }
    
    public function getDataArray($includes = NULL)
    {
        if ( $includes === NULL )
        {
            $includes = array();
        }
        
        $data = [];
        
        $data['ID'] = $this->getId();
        $data['QUIZ_ID'] = $this->getQuizID();
        $data['LOCKED_ON_COMPLETION'] = $this->getLockedOnCompletion();
        $data['TIME_TO_COMPLETE'] = $this->getTimeToComplete();
        $data['QUIZ_DATA'] = $this->getQuizData();
        $data['IS_USER_CAN_DISPLAY_CHART'] = $this->getIsUserCanDisplayChart();
        $data['IS_USER_CAN_DISPLAY_QA'] = $this->getIsUserCanDisplayQa();
        $data['IS_ENABLED'] = $this->getIsEnabled();
        $data['IS_USER_SEE_GOOD_ANSWER'] = $this->getIsUserSeeGoodAnswer();
        $data['ANSWER_JSON'] = $this->getAnswerJson();
        
        return $data;
    }
}
