<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\UserAuthentication;
use com\novaconcept\entity\UserAuthorization;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\utility\ApiConfig;
use com\novaconcept\utility\StorageSdk;
use DateTime;
use stdClass;

class QuizAveragesService extends AbstractCoreService {

    public function __construct($request, $bootstrap) {
        parent::__construct($request, $bootstrap);
    }

    private $CorporateUniqueID = [];
    private $GroupUniqueID = [];
    private $AgencyUniqueID = [];
    private $QuizUniqueID = [];
    private $UniqueUserID = [];

    private $data = [];

    public function get() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userAuthorization = new Permission();
        $accountId = $this->request->getPathParamByName('account_info_id');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired('is_agency_admin');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userAgencyPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);
        $this->GetUniqueID($this->request->getPostData());

        $data = [];

        if (array_search('none', explode(",", $this->request->getQueryParam("includes"))) !== FALSE) {
            foreach ($this->UniqueUserID as $user) {
                $userInfo = $this->bootstrap->getEntityManager()
                        ->find('com\novaconcept\entity\UserInfo', $user);
                if ($userInfo == NULL) {
                    $this->securityLog('user_not_found');
                    $this->response->setResponseStatus(404)
                            ->build();
                    return;
                }
                $securityTesterUser = FALSE;
                foreach ($userInfo->getDepartmentAuthorizationCollection() as $authorizationDepartment) {
                    if ($authorizationDepartment->getDepartmentInfo()->getDescription() == 'IS_AGENCY') {
                        $corpoUser = $authorizationDepartment->getDepartmentInfo()->getParent()->getParent();
                        foreach ($this->userInfo->getDepartmentAuthorizationCollection() as $authorizationDepartmentInfo) {
                            if ($authorizationDepartmentInfo->getDepartmentInfo()->getDescription() == 'IS_AGENCY') {
                                $corpoUserInfo = $authorizationDepartmentInfo->getDepartmentInfo()->getParent()->getParent();
                                if ($corpoUser == $corpoUserInfo) {
                                    $securityTesterUser = TRUE;
                                }
                            }
                        }
                    }
                }
                if ($securityTesterUser) {
                    foreach ($userInfo->getQuizResultsCollection() as $result) {
                        if ( $result->getProgressId()->getId() == 3 ){
                            $quiz_result = [];
                            $quiz_result['USER_ID'] = $userInfo->getId();
                            $quiz_result['ANSWERS'] = $result->getAnswers();
                            $quiz_result['QUIZ_SCORE'] = $result->getQuizScore();
                            $quiz_result['QUIZ_ID'] = $result->getQuizID()->getId();
                            $quizGroupQuizList = $this->bootstrap->getEntityManager()
                                    ->getRepository('com\novaconcept\entity\QuizGroupQuizList')
                                    ->findOneBy(array('quizId' => $result->getQuizID()->getId()));
                            $quiz_result['QUIZ_GROUP_ID'] = $quizGroupQuizList->getQuizGroupID();
                            $quiz_result['QUIZ_ORDER'] = $quizGroupQuizList->getOrderNB();
                            $quiz_result['QUIZ_NAME'] = $result->getQuizID()->getQuizID();
                            array_push($data, $quiz_result);
                        }
                    }
                }
            }
        }

        if (array_search('AGENCIES', explode(",", $this->request->getQueryParam("includes"))) !== FALSE) {
            $quiz_answers = [];
            $quiz_scores = [];
            $quiz_id = [];
            $agencies_averages = [];
            foreach ($this->AgencyUniqueID as $agency) {
                $agencyInfo = $this->bootstrap->getEntityManager()
                        ->find('com\novaconcept\entity\DepartmentInfo', $agency);
                if ($agencyInfo == NULL || $agencyInfo->getDescription() != 'IS_AGENCY') {
                    $this->securityLog('agency_not_found');
                    $this->response->setResponseStatus(404)
                            ->build();
                    return;
                }
                $securityTesterAgency = FALSE;
                foreach ($this->userInfo->getDepartmentAuthorizationCollection() as $authorizationDepartment) {
                    if ($authorizationDepartment->getDepartmentInfo()->getDescription() == 'IS_AGENCY') {
                        $corpoUser = $authorizationDepartment->getDepartmentInfo()->getParent()->getParent();
                        if ($corpoUser == $agencyInfo->getParent()->getParent()) {
                            $securityTesterAgency = TRUE;
                        }
                    }
                }
                if ($securityTesterAgency) {
                    foreach ($agencyInfo->getUserInfoCollection() as $userAgency) {
                        if ($userAgency->getQuizResultsCollection()->count() > 0) {
                            foreach ($userAgency->getQuizResultsCollection() as $quizResult) {
                                if (!isset($quiz_answers[$quizResult->getQuizID()->getId()])) {
                                    $quiz_answers[$quizResult->getQuizID()->getId()] = [];
                                    $quiz_scores[$quizResult->getQuizID()->getId()] = [];
                                    $quiz_id[] = $quizResult->getQuizID()->getId();
                                }

                                if ($quizResult->getProgressId()->getId() == 3) {
                                    $quiz_answers[$quizResult->getQuizID()->getId()][] = $quizResult->getAnswers();
                                    $quiz_scores[$quizResult->getQuizID()->getId()][] = $quizResult->getQuizScore();
                                }
                            }
                        }
                    }
                    foreach ($quiz_id as $quizId) {
                        if (array_search($quizId, $this->QuizUniqueID) !== FALSE && count($quiz_answers[$quizId]) > 0) {
                            $quiz_scores_averages = $this->CalculateScoresAverage($quiz_scores[$quizId]);
                            $quiz_answers_average = $this->CalculateAnswersAverage($quiz_answers[$quizId]);
                            $agencies_info = [];
                            $agencies_info[0] = "AGENCIES_AVERAGES";
                            $agencies_info[1] = $quizId; //Current QUIZ_ID
                            $agencies_info[2] = $agencyInfo->getId(); //Current AGENCY_ID
                            $agencies_info[3] = $quiz_scores_averages; //Current AGENCY_ID/QUIZ_ID scores averages
                            $agencies_info[4] = $quiz_answers_average; //Current AGENCY_ID/QUIZ_ID answers averages
                            array_push($data, $agencies_info);
                        }
                    }
                }
            }
        }

        if (array_search('GROUPS', explode(",", $this->request->getQueryParam("includes"))) !== FALSE) {
            foreach ($this->GroupUniqueID as $group) {
                $groupInfo = $this->bootstrap->getEntityManager()
                        ->find('com\novaconcept\entity\DepartmentInfo', $group);

                if ($groupInfo == NULL || $groupInfo->getDescription() != 'IS_GROUP') {
                    $this->securityLog('group_not_found');
                    $this->response->setResponseStatus(404)
                            ->build();
                    return;
                }
                $quiz_groups_answers = [];
                $quiz_groups_scores = [];
                $quiz_id = [];
                $securityTesterGroup = FALSE;
                foreach ($this->userInfo->getDepartmentAuthorizationCollection() as $authorizationDepartment) {
                    if ($authorizationDepartment->getDepartmentInfo()->getDescription() == 'IS_AGENCY') {
                        $corpoUser = $authorizationDepartment->getDepartmentInfo()->getParent()->getParent();
                        if ($corpoUser == $groupInfo->getParent()) {
                            $securityTesterGroup = TRUE;
                        }
                    }
                }
                if ($securityTesterGroup) {
                    foreach ($groupInfo->getChildrenCollection() as $agencyDepartment) {
                        foreach ($agencyDepartment->getDepartmentAuthorizationCollection() as $department) {
                            if ($department->getUserInfo()->getQuizResultsCollection()->count() > 0) {
                                foreach ($department->getUserInfo()->getQuizResultsCollection() as $quizResult) {
                                    if (!isset($quiz_groups_answers[$quizResult->getQuizID()->getId()])) {
                                        $quiz_groups_answers[$quizResult->getQuizID()->getId()] = [];
                                        $quiz_groups_scores[$quizResult->getQuizID()->getId()] = [];
                                        $quiz_id[] = $quizResult->getQuizID()->getId();
                                    }

                                    if ($quizResult->getProgressId()->getId() == 3) {
                                        $quiz_groups_answers[$quizResult->getQuizID()->getId()][] = $quizResult->getAnswers();
                                        $quiz_groups_scores[$quizResult->getQuizID()->getId()][] = $quizResult->getQuizScore();
                                    }
                                }
                            }
                        }
                    }
                    foreach ($quiz_id as $quizId) {
                        if (array_search($quizId, $this->QuizUniqueID) !== FALSE && count($quiz_groups_answers[$quizId]) > 0) {
                            $quiz_groups_scores_averages = $this->CalculateScoresAverage($quiz_groups_scores[$quizId]);
                            $quiz_answers_average = $this->CalculateAnswersAverage($quiz_groups_answers[$quizId]);
                            $groups_info = [];
                            $groups_info[0] = "GROUPS_AVERAGES";
                            $groups_info[1] = $quizId; //Current QUIZ_ID
                            $groups_info[2] = $groupInfo->getId(); //Current AGENCY_ID
                            $groups_info[3] = $quiz_groups_scores_averages; //Current GROUP_ID/QUIZ_ID scores averages
                            $groups_info[4] = $quiz_answers_average; //Current GROUP_ID/QUIZ_ID answers averages
                            array_push($data, $groups_info);
                        }
                    }
                }
            }
        }

        if (array_search('CORPORATES', explode(",", $this->request->getQueryParam("includes"))) !== FALSE) {
            $corpo = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $quiz_corpo_answers = [];
            $quiz_corpo_scores = [];
            $quiz_id = [];
            $corpo_averages = [];
            foreach ($this->CorporateUniqueID as $corporate) {
                $corporateInfo = $this->bootstrap->getEntityManager()
                        ->find('com\novaconcept\entity\DepartmentInfo', $corporate);

                if ($corporateInfo == NULL || $corporateInfo->getDescription() != 'IS_CORPO') {
                    $this->securityLog('corporate_not_found');
                    $this->response->setResponseStatus(404)
                            ->build();
                    return;
                }
                $securityTesterCorpo = FALSE;
                foreach ($this->userInfo->getDepartmentAuthorizationCollection() as $authorizationDepartment) {
                    if ($authorizationDepartment->getDepartmentInfo()->getDescription() == 'IS_AGENCY') {
                        $corpoUser = $authorizationDepartment->getDepartmentInfo()->getParent()->getParent();
                        if ($corpoUser == $corporateInfo) {
                            $securityTesterCorpo = TRUE;
                        }
                    }
                }
                if ($securityTesterCorpo) {
                    foreach ($corporateInfo->getChildrenCollection() as $groupDepartment) {
                        foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                            foreach ($agencyDepartment->getDepartmentAuthorizationCollection() as $department) {
                                if ($department->getUserInfo()->getQuizResultsCollection()->count() > 0) {
                                    foreach ($department->getUserInfo()->getQuizResultsCollection() as $quizResult) {
                                        if (!isset($quiz_corpo_answers[$quizResult->getQuizID()->getId()])) {
                                            $quiz_corpo_answers[$quizResult->getQuizID()->getId()] = [];
                                            $quiz_corpo_scores[$quizResult->getQuizID()->getId()] = [];
                                            $quiz_id[] = $quizResult->getQuizID()->getId();
                                        }

                                        if ($quizResult->getProgressId()->getId() == 3) {
                                            $quiz_corpo_answers[$quizResult->getQuizID()->getId()][] = $quizResult->getAnswers();
                                            $quiz_corpo_scores[$quizResult->getQuizID()->getId()][] = $quizResult->getQuizScore();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    foreach ($quiz_id as $quizId) {
                        if (array_search($quizId, $this->QuizUniqueID) !== FALSE && count($quiz_corpo_answers[$quizId]) > 0) {
                            $quiz_corpo_scores_averages = $this->CalculateScoresAverage($quiz_corpo_scores[$quizId]);
                            $quiz_answers_average = $this->CalculateAnswersAverage($quiz_corpo_answers[$quizId]);
                            $corpo_info[0] = "CORPORATES_AVERAGES";
                            $corpo_info[1] = $quizId; //Current QUIZ_ID
                            $corpo_info[2] = $corporateInfo->getId(); //Current CORPO_ID
                            $corpo_info[3] = $quiz_corpo_scores_averages; //Current CORPO_ID/QUIZ_ID scores averages
                            $corpo_info[4] = $quiz_answers_average; //Current CORPO_ID/QUIZ_ID answers averages
                            array_push($data, $corpo_info);
                        }
                    }
                }
            }
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function CalculateScoresAverage($quiz_scores_array) {
        $quiz_scores_array_average = array();
        for ($i = 0; $i < count($quiz_scores_array); $i++) {
            $split_array = explode(',', $quiz_scores_array[$i]);
            for ($j = 0; $j < count($split_array); $j++) {
                $r_pos = strpos($split_array[$j], 'r');
                $m_pos = strpos($split_array[$j], 'm');
                //get section, result and maximum #
                $cur_section = substr($split_array[$j], 1, $r_pos - 1);
                $cur_result = substr($split_array[$j], $r_pos + 1, 1);
                $cur_max = substr($split_array[$j], $m_pos + 1);
                if (!isset($quiz_scores_array_average[$cur_section][0])){
                    $quiz_scores_array_average[$cur_section][0] = 0;
                }
                $quiz_scores_array_average[$cur_section][0] += $cur_result;
                //Keep highest MAX found
                if (!isset($quiz_scores_array_average[$cur_section][1])){
                    $quiz_scores_array_average[$cur_section][1] = 0;
                }
                if ($cur_max > $quiz_scores_array_average[$cur_section][1]) {
                    $quiz_scores_array_average[$cur_section][1] = $cur_max;
                }
            }
        }
        //Calculate final average
        $split_array2 = explode(',', $quiz_scores_array[0]);
        for ($m = 0; $m < count($split_array2); $m++) {
            $quiz_scores_array_average[$m][0] = $quiz_scores_array_average[$m][0] / count($quiz_scores_array);
        }

        return $quiz_scores_array_average;
    }

    public function CalculateAnswersAverage($quiz_answers_array) {
        // ******************* TO verify OUTPUT... *****************************
        $quiz_asnwers_array_average = array();

        for ($i = 0; $i < count($quiz_answers_array); $i++) {
            $split_array = explode(',', $quiz_answers_array[$i]);
            for ($j = 0; $j < count($split_array); $j++) {
                if ( preg_match('/s[0-9]{1}q[0-9]{1}a[0-9]{1}/', $split_array[$j]) == TRUE ){
                    if (!isset($quiz_asnwers_array_average[substr($split_array[$j], 0, 4)])){
                        $quiz_asnwers_array_average[substr($split_array[$j], 0, 4)] = array();
                        $quiz_asnwers_array_average[substr($split_array[$j], 0, 4)]['total'] = 0;
                    }
                    if (!isset($quiz_asnwers_array_average[substr($split_array[$j], 0, 4)][substr($split_array[$j], 5, 1)])){
                        $quiz_asnwers_array_average[substr($split_array[$j], 0, 4)][substr($split_array[$j], 5, 1)] = 0;
                    }
                    $quiz_asnwers_array_average[substr($split_array[$j], 0, 4)][substr($split_array[$j], 5, 1)] += 1;
                    $quiz_asnwers_array_average[substr($split_array[$j], 0, 4)]['total'] += 1;
                }
            }
        }
        
        return $quiz_asnwers_array_average;
    }

    function GetUniqueID($json_data) {
        $CorporateUniqueID = [];
        $GroupUniqueID = [];
        $AgencyUniqueID = [];
        $QuizUniqueID = [];
        $UniqueUserID = [];

        for ($i = 0; $i < count($json_data); $i++) {
            $CorporateUniqueID[$i] = $json_data[$i][12]; //CORPORATE_ID
            $GroupUniqueID[$i] = $json_data[$i][14]; //GROUP_ID
            $AgencyUniqueID[$i] = $json_data[$i][16]; //AGENCY_ID
            $QuizUniqueID[$i] = $json_data[$i][1]; //QUIZ_ID
            $UniqueUserID[$i] = $json_data[$i][2]; //USER_ID
        }

        //make unique values
        $this->CorporateUniqueID = array_values(array_unique($CorporateUniqueID, SORT_REGULAR));
        $this->GroupUniqueID = array_values(array_unique($GroupUniqueID));
        $this->AgencyUniqueID = array_values(array_unique($AgencyUniqueID, SORT_REGULAR));
        $this->QuizUniqueID = array_values(array_unique($QuizUniqueID, SORT_REGULAR));
        $this->UniqueUserID = array_values(array_unique($UniqueUserID, SORT_REGULAR));
    }

}