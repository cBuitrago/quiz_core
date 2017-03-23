<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\QuizResults;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\UserAuthentication;
use com\novaconcept\entity\UserAuthorization;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\entity\UserQuizGroup;
use com\novaconcept\utility\ApiConfig;
use com\novaconcept\utility\StorageSdk;
use com\novaconcept\utility\Constants;
use DateTime;
use stdClass;

class QuizResultsService extends AbstractCoreService {

    public function __construct($request, $bootstrap) {
        parent::__construct($request, $bootstrap);
    }

    public function add() {

        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired(Constants::AGENCY);
        $userPermission = new Permission();
        $userPermission->addRequired(Constants::USER);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userAgencyPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }
        $request = $this->request->getPostData();
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::ACCOUNT_INFO_REP, $accountId);

        $quizInfo = $this->bootstrap->getEntityManager()
                ->getRepository(Constants::QUIZ_REP)
                ->findOneBy(array('quizId' => $request->QUIZ_ID));
        if ($quizInfo == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        if ((($request->END_DATE - $request->START_DATE ) < $quizInfo->getTimeToComplete()) ||
                ((($request->END_DATE - $request->START_DATE ) == $quizInfo->getTimeToComplete()) && ( count(explode(",", $request->ANSWERS)) > 1 ))) {
            $progress = 3;
        } else {
            $progress = 2;
            $request->ANSWERS = "";
            $request->QUIZ_SCORE = "";
        }

        $progressId = $this->bootstrap->getEntityManager()
                ->find(Constants::PROGRESS_INFO_REP, $progress);
        if ($progressId == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        $quizResultsConflict = $this->bootstrap->getEntityManager()
                ->getRepository(Constants::QUIZ_RESULTS_REP)
                ->createQueryBuilder('u')
                ->where('u.quizId = :quizId')
                ->andWhere('u.userInfo = :userInfo')
                ->setParameter("userInfo", $this->userInfo)
                ->setParameter("quizId", $quizInfo)
                ->getQuery()
                ->getOneOrNullResult();
        if ($quizResultsConflict != NULL) {
            $this->securityLog(Constants::CONFLICT_STR);
            $this->response->setResponseStatus(Constants::CONFLICT)
                    ->build();
            return;
        }

        $validation = FALSE;
        $departmentInfo = $this->userInfo->getDepartmentInfoCollection()->first();
        foreach ($departmentInfo->getQuizAuthorizationCollection() as $quizAuthorization) {
            if ($quizAuthorization->getQuizInfo() == $quizInfo) {
                $validation = TRUE;
            }
        }

        if ($validation === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $startDate = new DateTime();
        $startDate->setTimestamp($request->START_DATE);
        $endDate = new DateTime();
        $endDate->setTimestamp($request->END_DATE);
        //$startDate = DateTime::createFromFormat('Y-m-d', $this->request->getPostData()->START_DATE);
        //$endDate = DateTime::createFromFormat('Y-m-d', $this->request->getPostData()->END_DATE);
        if ($progress == 3) {
            $goodAnswers = explode("|", $quizInfo->getAnswerJson());
            for ($j = 0; $j < count($goodAnswers); $j++) {
                $goodAnswers[$j] = explode(";", $goodAnswers[$j]);
                for ($m = 0; $m < count($goodAnswers[$j]); $m++) {
                    $goodAnswers[$j][$m] = explode(",", $goodAnswers[$j][$m]);
                }
            }

            $resultRawData = explode(",", $request->ANSWERS);
            $resultsCompiledData = new stdClass();

            $section = 0;
            $score = 0;
            $resultCompiledData = new stdClass();
            $resultCompiledData->{'section' . $section} = new stdClass();
            $newSection = 0;
            $sectionCounter = 0;
            $questionCounter = 0;
            $maxScorePerSection = array();
            
            $item =  count($resultRawData);
            for ($i = 0; $i <= $item; $i++) {
                    $newSection = $this->getDataFromString($resultRawData[$i], "s", "q");
                    $question = $this->getDataFromString($resultRawData[$i], "q", "a");
                    $answer = $this->getDataFromString($resultRawData[$i], "a");
                    $weight = $goodAnswers[$newSection][$question][$answer];
                
                if ($newSection != $section) {
                    if ($score < 0)
                        $score = 0;

                    $resultCompiledData->{'section' . $section}->{'maxScore'} = $this->sumUpArray($maxScorePerSection);
                    $maxScorePerSection = array();
                    $resultCompiledData->{'section' . $section}->{'score'} = $score;
                    $resultCompiledData->{'section' . $section}->{'questionLength'} = $questionCounter;
                    $section = $newSection;
                    $questionCounter = 0;
                    $resultCompiledData->{'section' . $section} = new stdClass();

                    $score = 0;
                    $sectionCounter++;
                }
                
                $score += (int) $weight;
                if (!isset($resultsCompiledData->{'section' . $section})) {
                    $resultsCompiledData->{'section' . $section} = new stdClass();
                }
                
                $resultsCompiledData->{'section' . $section}->{'question' . $question} = new stdClass();
                $resultsCompiledData->{'section' . $section}->{'question' . $question}->{'answer'} = $answer;
                $resultsCompiledData->{'section' . $section}->{'question' . $question}->{'score'} = $weight;
                $resultsCompiledData->{'section' . $section}->{'question' . $question}->{'maxScore'} = $this->getHighestInArray($goodAnswers[$newSection][$question]);
                array_push($maxScorePerSection, $resultsCompiledData->{'section' . $section}->{'question' . $question}->{'maxScore'});
                $questionCounter++;
            }

            if ($score < 0)
                $score = 0;

            $resultCompiledData->{'section' . $section}->{'maxScore'} = $this->sumUpArray($maxScorePerSection);
            $maxScorePerSection = array();
            $resultCompiledData->{'section' . $section}->{'score'} = $score;
            $resultCompiledData->{'section' . $section}->{'questionLength'} = $questionCounter;


            $resultCompiledData->{'sectionLength'} = $sectionCounter;


            //Creer les resultas de section s##r##m##
            $sectionResultString = '';
            for ($k = 0; $k < $resultCompiledData->{'sectionLength'}; $k++) {
                if ($k == (int) $resultCompiledData->{'sectionLength'} - 1) {
                    $sectionResultString.= "s" . $k . "r" . $resultCompiledData->{'section' . $k}->{'score'} . "m" . $resultCompiledData->{'section' . $k}->{'maxScore'};
                } else {
                    $sectionResultString.= "s" . $k . "r" . $resultCompiledData->{'section' . $k}->{'score'} . "m" . $resultCompiledData->{'section' . $k}->{'maxScore'} . ",";
                }
            }
            $request->QUIZ_SCORE = $sectionResultString;
        }

        $quizResults = new QuizResults();
        $quizResults->mapPostData($request);
        $quizResults->setQuizID($quizInfo)
                ->setUserID($this->userInfo)
                ->setProgressId($progressId)
                ->setStartDate($startDate)
                ->setEndDate($endDate);

        $this->bootstrap->getEntityManager()->persist($quizResults);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::CREATED);
        $this->response->setResponseStatus(Constants::CREATED)
                ->setResponseData($quizInfo->getId())
                ->build();
    }

    // HELPER FUNCTIONS
    public function getDataFromString($data, $valA, $valB = null) {

        $posA = strrpos($data, $valA) + 1;

        if ($valB != null) {
            $posB = strrpos($data, $valB);
            return substr($data, $posA, $posB - $posA);
        } else {
            return substr($data, $posA);
        }
    }

    public function getHighestInArray($arrayToSort) {
        $highestValue = $arrayToSort[0];
        for ($m = 0; $m < count($arrayToSort); $m++) {
            if ($m == 0) {
                $highestValue = $arrayToSort[$m];
            } else if ($arrayToSort[$m] > $highestValue) {
                $highestValue = $arrayToSort[$m];
            }
        }
        return $highestValue;
    }

    public function sumUpArray($arrayToAdd) {
        $arraySum = 0;
        for ($q = 0; $q < count($arrayToAdd); $q++) {
            $arraySum += $arrayToAdd[$q];
        }

        return $arraySum;
    }

}