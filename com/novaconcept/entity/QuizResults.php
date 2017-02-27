<?php

namespace com\novaconcept\entity;

use com\novaconcept\utility\ApiConfig;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * QuizResults
 *
 * @ORM\Table(name="quiz_results")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class QuizResults extends AbstractEntity 
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
     * @var quizId
     * @ORM\ManyToOne(targetEntity="Quiz", inversedBy="quizResultsCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="QUIZ_ID", referencedColumnName="ID")
     * })
     */
    private $quizId;
    
    /**
     * @var UserInfo
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="quizResultsCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="USER_ID", referencedColumnName="PK_id")
     * })
     */
    private $userInfo;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="START_DATE", type="datetime", nullable=true)
     */
    private $startDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="END_DATE", type="datetime", nullable=true)
     */
    private $endDate;
   
    /**
     * @var ProgressInfo
     * @ORM\ManyToOne(targetEntity="ProgressInfo", inversedBy="quizResultsCollection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="PROGRESS_ID", referencedColumnName="ID")
     * })
     */
    private $progressId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ANSWERS", type="string", nullable=false)
     */
    private $answers;
    
    /**
     * @var string
     *
     * @ORM\Column(name="QUIZ_SCORE", type="string", nullable=false)
     */
    private $quizScore;
    
    /**
     * @var string
     *
     * @ORM\Column(name="PREVIOUS_ANSWERS", type="string", nullable=true)
     */
    private $previousAnswers;
    
    /**
     * @var string
     *
     * @ORM\Column(name="PREVIOUS_SCORES", type="string", nullable=true)
     */
    private $previousScores;

    public function __construct()
    {
        
    }

    public function getId() { return $this->id; }
    public function setQuizID($quizId) { $this->quizId = $quizId; return $this; }
    public function getQuizID() { return $this->quizId; }
    public function setUserID($userInfo) { $this->userInfo = $userInfo; return $this; }
    public function getUserID() { return $this->userInfo; }
    public function setStartDate($startDate) { $this->startDate = $startDate; return $this; }
    public function getStartDate() { return $this->startDate; }
    public function setEndDate($endDate) { $this->endDate = $endDate; return $this; }
    public function getEndDate() { return $this->endDate; }
    public function setProgressId($progressId) { $this->progressId = $progressId; return $this; }
    public function getProgressId() { return $this->progressId; }
    public function setAnswers($answers) { $this->answers = $answers; return $this; }
    public function getAnswers() { return $this->answers; }
    public function setQuizScore($quizScore) { $this->quizScore = $quizScore; return $this; }
    public function getQuizScore() { return $this->quizScore; }
    public function setPreviousAnswers($previousAnswers) { $this->previousAnswers = $previousAnswers; return $this; }
    public function getPreviousAnswers() { return $this->previousAnswers; }
    public function setPreviousScores($previousScores) { $this->previousScores = $previousScores; return $this; }
    public function getPreviousScores() { return $this->previousScores; }

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
        $this->setAnswers($requestData->ANSWERS); 
        $this->setQuizScore($requestData->QUIZ_SCORE); 
        if ( isset($requestData->PREVIOUS_ANSWERS) )
        { $this->setPreviousAnswers($requestData->PREVIOUS_ANSWERS);}
        else { $this->setPreviousAnswers(NULL); }
        if ( isset($requestData->PREVIOUS_SCORES) )
        { $this->setPreviousScores($requestData->PREVIOUS_SCORES); }
        else { $this->setPreviousScores(NULL); }
    }
    
    public function mergePostData($requestData)
    {
        if ( isset($requestData->ANSWERS) )
            $this->setAnswers($requestData->ANSWERS);
        
        if ( isset($requestData->QUIZ_SCORE) )
            $this->setQuizScore($requestData->QUIZ_SCORE);
                
        if ( isset($requestData->previousAnswers) )
            $this->setPreviousAnswers($requestData->previousAnswers);
        
        if ( isset($requestData->previousScores) )
            $this->setPreviousScores($requestData->previousScores);
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
        {
            $includes = array();
        }
        
        /*$data = new stdClass();
        
        $data->id = $this->getId();
        $data->quizId = $this->getQuizID();
        $data->userInfo = $this->getUserID()->getId();
        $data->startDate = $this->getStartDate();
        $data->endDate = $this->getEndDate();
        $data->progressId = $this->getProgressId();
        $data->answers = $this->getAnswers();
        $data->quizScore = $this->getQuizScore();
        $data->previousAnswers = $this->getPreviousAnswers();
        $data->previousScores = $this->getPreviousScores();*/
        /*$data = [];
        
        $data['ID'] = $this->getId();
        $data['QUIZ_ID'] = $this->getQuizID()->getId();
        $data['USER_ID'] = $this->getUserID()->getId();
        $data['START_DATE'] = $this->getStartDate() ? $this->getStartDate()->format('Y-m-d') : null;
        $data['END_DATE'] = $this->getEndDate() ? $this->getEndDate()->format('Y-m-d') : null;
        $data['PROGRESS_ID'] = $this->getProgressId()->getId();
        $data['ANSWERS'] = $this->getAnswers();
        $data['QUIZ_SCORE'] = $this->getQuizScore();
        $data['PREVIOUS_ANSWERS'] = $this->getPreviousAnswers();
        $data['PREVIOUS_SCORES'] = $this->getPreviousScores();*/
        
        $data = [];
        
        $data[] = $this->getId();
        $data[] = $this->getQuizID()->getId();
        $data[] = $this->getUserID()->getId();
        $data[] = $this->getStartDate() ? $this->getStartDate()->format('Y-m-d') : null;
        $data[] = $this->getEndDate() ? $this->getEndDate()->format('Y-m-d') : null;
        $data[] = $this->getProgressId()->getId();
        $data[] = $this->getAnswers();
        $data[] = $this->getQuizScore();
        $data[] = $this->getPreviousAnswers();
        $data[] = $this->getPreviousScores();
        
        return $data;
    }
}
