<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\Quiz;
use com\novaconcept\entity\QuizAuthorization;
use com\novaconcept\entity\QuizResults;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\UserAuthentication;
use com\novaconcept\entity\UserAuthorization;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\entity\UserQuizGroup;
use DateTime;
use stdClass;

class QuizService extends AbstractCoreService {

    public function __construct($request, $bootstrap) {
        parent::__construct($request, $bootstrap);
    }

    public function add() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $validator = FALSE;
        $quizValidator = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\Quiz')
                ->findOneBy(array('quizId' => $this->request->getPostData()->quizId));
        if ($quizValidator != NULL) {
            $this->securityLog('quiz_already_exists');
            $this->response->setResponseStatus(409)
                    ->build();
            return;
        }

        $quizInfo = new Quiz();
        $quizInfo->mapPostData($this->request->getPostData());

        $this->bootstrap->getEntityManager()->persist($quizInfo);
        $this->bootstrap->getEntityManager()->flush();

        foreach ($this->request->getPostData()->agencies as $agency) {
            $agencyInfo = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\DepartmentInfo', $agency);
            if ($agencyInfo == NULL) {
                continue;
            }
            $newQuizAuthorization = new QuizAuthorization();
            $newQuizAuthorization->setDepartmentInfo($agencyInfo)
                    ->setQuizInfo($quizInfo)
                    ->setStartDate(new DateTime())
                    ->setEndDate(new DateTime());

            $this->bootstrap->getEntityManager()->persist($newQuizAuthorization);
        }

        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(201);
        $this->response->setResponseStatus(201)
                ->build();
    }

    public function edit() {

        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ( $this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE ) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $quizInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\Quiz', $this->request->getPathParamByName('id'));
        if ( $quizInfo == NULL ) {
            $this->securityLog('quiz_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        $validator = FALSE;
        $quizValidator = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\Quiz')
                ->findOneBy(array('quizId' => $this->request->getPostData()->QUIZ_ID));
        if ( $quizValidator != NULL && $quizValidator != $quizInfo ) {
            $this->securityLog('quiz_already_exists');
            $this->response->setResponseStatus(409)
                    ->build();
            return;
        }

        $quizInfo->mergePostData($this->request->getPostData());
        $this->bootstrap->getEntityManager()->merge($quizInfo);
        $this->bootstrap->getEntityManager()->flush();
        $corpo = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();

        $authorizations = array_unique($this->request->getPostData()->AGENCY_QUIZ);
        foreach ( $quizInfo->getQuizAuthorizationCollection() as $quizAuthorization ) {
            if ( $quizAuthorization->getDepartmentInfo()->getParent()->getParent() === $corpo ) {
                $cle = array_search($quizAuthorization->getDepartmentInfo()->getId(), $authorizations);
                if ( $cle === FALSE ) {
                    $this->bootstrap->getEntityManager()->remove($quizAuthorization);
                } else {
                    unset($authorizations[$cle]);
                }
            }
        }
        $this->bootstrap->getEntityManager()->flush();

        foreach ( $authorizations as $departmentId ) {
            $departmentInfo = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\DepartmentInfo', $departmentId);
            if ( $departmentInfo == NULL ) {
                continue;
            }
            if ( $departmentInfo->getParent()->getParent() != $corpo ) {
                continue;
            }
            $newAuthorization = new QuizAuthorization();
            $newAuthorization->setDepartmentInfo($departmentInfo)
                    ->setQuizInfo($quizInfo)
                    ->setStartDate(new DateTime())
                    ->setEndDate(new DateTime());
            $this->bootstrap->getEntityManager()->persist($newAuthorization);
            $this->bootstrap->getEntityManager()->flush();
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->build();
    }

    public function test() {
        //set_time_limit ( 150 );
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userAuthorization = new Permission();
        $userAuthorization->addRequired('is_god');
        $accountId = $this->request->getPathParamByName('account_info_id');

        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);

        $quiz = array("1", "2");
        $is_completed = array("1", "2", "3");
        $passed_data = 20000;
        $recordnb = 0;
        $user_id_nb = 1;
        //Loop to create a requested new records
        for ($i = 1; $i < ($passed_data / 2); $i++) {
            //Create USER_NAME
            $userData = new stdClass();
            $userData->username = "TEST_USER_" . $user_id_nb;
            $userData->name = "user_";
            $userData->firstName = "_" . $user_id_nb;
            $userData->password = 'nova';
            $userData->forceChange = true;
            $userInfo = new UserInfo();
            $userInfo->mapPostData($userData);
            $this->bootstrap->getEntityManager()->persist($userInfo);
            $this->bootstrap->getEntityManager()->flush();

            $userAuthentication = new UserAuthentication();
            $userAuthentication->mapPostData($userData)
                    ->setUserInfo($userInfo);
            $userInfo->setUserAuthentication($userAuthentication);
            $this->bootstrap->getEntityManager()->persist($userAuthentication);
            $this->bootstrap->getEntityManager()->flush();

            $userAccount = new UserAccount();
            $userAccount->setAccountInfo($accountInfo)
                    ->setUserInfo($userInfo);
            $this->bootstrap->getEntityManager()->persist($userAccount);
            $this->bootstrap->getEntityManager()->flush();

            $permissionUser = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\UserPermission', 17);

            $userAuthorization = new UserAuthorization();
            $userAuthorization->setAccountInfo($accountInfo)
                    ->setUserInfo($userInfo)
                    ->setUserPermission($permissionUser);
            $this->bootstrap->getEntityManager()->persist($userAuthorization);
            $this->bootstrap->getEntityManager()->flush();

            $departmentInfo = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\DepartmentInfo', rand(423, 432));
            $departmentAuthorization = new DepartmentAuthorization();
            $departmentAuthorization->setDepartmentInfo($departmentInfo);
            $departmentAuthorization->setIsRecursive(FALSE);
            $departmentAuthorization->setUserInfo($userInfo);
            $this->bootstrap->getEntityManager()->persist($departmentAuthorization);
            $this->bootstrap->getEntityManager()->flush();

            //Add user to "user_quiz_group" table
            $userQuizGroup = new UserQuizGroup();
            $userQuizGroup->setQuizGroupID(1);
            $userQuizGroup->setUserId($userInfo->getId());
            $this->bootstrap->getEntityManager()->persist($userQuizGroup);
            $this->bootstrap->getEntityManager()->flush();

            //Random for completed level
            $quiz_completed_id = rand(1, 3);

            //Create answers if quiz completed state
            $quiz_answers = "";
            $quiz_score = "";
            if ($quiz_completed_id == 3) {
                $quiz_answers = $this->CreateRandomAnswer();
                $quiz_score = $this->GetScoreFromAnswers($quiz_answers);
            }

            $start_date = new DateTime();
            $end_date = new DateTime();

            $quizId_01 = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\Quiz', 1);
            $progressInfo = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\ProgressInfo', $quiz_completed_id);

            //Add quiz_results_01 data to DB
            $mapQuizResults01 = new stdClass();
            $mapQuizResults01->quizId = $quizId_01;
            $mapQuizResults01->userInfo = $userInfo;
            $mapQuizResults01->startDate = $quiz_completed_id == 3 ? $start_date : NULL;
            $mapQuizResults01->endDate = $quiz_completed_id == 3 ? $end_date : NULL;
            $mapQuizResults01->progressId = $progressInfo;
            $mapQuizResults01->answers = $quiz_answers;
            $mapQuizResults01->quizScore = $quiz_score;
            $mapQuizResults01->previousAnswers = NULL;
            $mapQuizResults01->previousScores = NULL;

            $quizResults01 = new QuizResults();
            $quizResults01->mapPostData($mapQuizResults01);
            $this->bootstrap->getEntityManager()->persist($quizResults01);
            $this->bootstrap->getEntityManager()->flush();

            //Set quiz_02 complete state based on quiz_01 completion
            $quiz_answers = "";
            $quiz_score = "";
            if ($quiz_completed_id == 3) {
                //Random for completed level
                $quiz_02_completed_id = rand(1, 3);
                if ($quiz_02_completed_id == 3) {
                    $quiz_answers = $this->CreateRandomAnswer();
                    $quiz_score = $this->GetScoreFromAnswers($quiz_answers);
                }
            } else {
                $quiz_02_completed_id = 0; //Not started
            }
            $quizId_02 = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\Quiz', 2);
            $progressInfo1 = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\ProgressInfo', $quiz_completed_id);
            //Add quiz_02 data to DB
            $mapQuizResults02 = new stdClass();
            $mapQuizResults02->quizId = $quizId_02;
            $mapQuizResults02->userInfo = $userInfo;
            $mapQuizResults02->startDate = $quiz_completed_id == 3 ? new DateTime() : NULL;
            $mapQuizResults02->endDate = $quiz_completed_id == 3 ? new DateTime() : NULL;
            $mapQuizResults02->progressId = $progressInfo1;
            $mapQuizResults02->answers = $quiz_answers;
            $mapQuizResults02->quizScore = $quiz_score;
            $mapQuizResults02->previousAnswers = NULL;
            $mapQuizResults02->previousScores = NULL;

            $quizResults02 = new QuizResults();
            $quizResults02->mapPostData($mapQuizResults02);
            $this->bootstrap->getEntityManager()->persist($quizResults02);
            $this->bootstrap->getEntityManager()->flush();

            $user_id_nb++;
        }
        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->build();
    }

    public function getAll() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userAuthorization = new Permission();
        //$userAuthorization->addRequired('is_god');
        $accountId = $this->request->getPathParamByName('account_info_id');

        if ($this->isAuthenticated($clientAuthorization, $userAuthorization, $accountId) === FALSE) {
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);

        $table = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\Quiz')
                ->findAll();

        $result_array = [];
        foreach ($table as $group) {
            $result_array[] = array($group->getId(), $group->getQuizID(),
                $group->getLockedOnCompletion(), $group->getTimeToComplete(),
                $group->getQuizData(), $group->getIsUserCanDisplayChart(),
                $group->getIsUserCanDisplayQa(), $group->getIsEnabled(),
                $group->getIsUserSeeGoodAnswer(), $group->getAnswerJson());
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($result_array)
                ->build();
    }

    public function get() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired('is_agency_admin');
        $userPermission = new Permission();
        $userPermission->addRequired('is_user');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userAgencyPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $departmentInfo = $this->userInfo->getDepartmentInfoCollection()->first();
        $data = [];
        foreach ($departmentInfo->getQuizAuthorizationCollection() as $authorization) {
            $result_array = $authorization->getQuizInfo()->getDataArray();
            $result_array['START_DATE'] = $authorization->getStartDate();
            $result_array['END_DATE'] = $authorization->getEndDate();
            foreach ($this->userInfo->getQuizResultsCollection() as $quizResult){
                if ($quizResult->getQuizID() ==  $authorization->getQuizInfo()){
                    $result_array['RESULT'] = TRUE;
                }
            }
            array_push($data, $result_array);
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function getQuizCorpo() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $corpoInfo = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
        $data = [];
        $allQuiz = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\Quiz')
                ->findAll();
        foreach ($allQuiz as $quiz) {
            foreach ($quiz->getQuizAuthorizationCollection() as $authorization) {
                if ($authorization->getDepartmentInfo()->getParent()->getParent() == $corpoInfo) {
                    $result_array = $authorization->getQuizInfo()->getDataArray();
                    $result_array['START_DATE'] = $authorization->getStartDate();
                    $result_array['END_DATE'] = $authorization->getEndDate();
                    array_push($data, $result_array);
                    break;
                }
            }
        }
        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function getById() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired('is_agency_admin');
        $userPermission = new Permission();
        $userPermission->addRequired('is_user');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userAgencyPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $validator = FALSE;
        $quizInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\Quiz', $this->request->getPathParamByName('id'));
        if ($quizInfo == NULL) {
            $this->securityLog('quiz_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        $departmentInfo = $this->userInfo->getDepartmentInfoCollection()->first();
        $corpo = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
        $data = [];
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            foreach ( $quizInfo->getQuizAuthorizationCollection() as $authorization ) {
                if ($authorization->getDepartmentInfo()->getParent()->getParent() == $corpo) {
                    $data = $authorization->getQuizInfo()->getDataArray();
                    $data['START_DATE'] = $authorization->getStartDate();
                    $data['END_DATE'] = $authorization->getEndDate();
                    $validator = TRUE;
                    foreach ($this->userInfo->getQuizResultsCollection() as $quizResult){
                        if ($quizResult->getQuizID() ==  $authorization->getQuizInfo()){
                            $data['RESULT'] = TRUE;
                            $data['RESULT_PROGRESS_ID'] = $quizResult->getProgressId()->getId();
                        }
                    }
                    break;
                }
            }
        } else {
            foreach ($departmentInfo->getQuizAuthorizationCollection() as $authorization) {
                if ($authorization->getQuizInfo() == $quizInfo) {
                    $data = $authorization->getQuizInfo()->getDataArray();
                    $data['START_DATE'] = $authorization->getStartDate();
                    $data['END_DATE'] = $authorization->getEndDate();
                    $validator = TRUE;
                    foreach ($this->userInfo->getQuizResultsCollection() as $quizResult){
                        if ($quizResult->getQuizID() ==  $authorization->getQuizInfo()){
                            $data['RESULT'] = TRUE;
                            $data['RESULT_PROGRESS_ID'] = $quizResult->getProgressId()->getId();
                        }
                    }
                    break;
                }
            }
        }
        if ($validator === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function getQuizIdAgencies() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $quizInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\Quiz', $this->request->getPathParamByName('id'));
        if ($quizInfo == NULL) {
            $this->securityLog('quiz_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        $data = [];

        $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
        foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
            foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                if ($agencyDepartment->getDescription() == "IS_AGENCY") {
                    $agency = $agencyDepartment->getData();
                    foreach ($agencyDepartment->getQuizAuthorizationCollection() as $quizAuthorization) {
                        if ($quizAuthorization->getQuizInfo() == $quizInfo) {
                            $agency->quizAuthorization = TRUE;
                            break;
                        }
                    }
                    array_push($data, $agency);
                }
            }
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function CreateRandomAnswer() {
        $answers_nb_sections = 7;
        $answers_nb_questons_per_section = 3;
        $answers = array("0", "1", "2", "3");
        $weights = array("-1", "0", "1", "2");

        //Randomize answers
        $current_asnwers = "";
        for ($j = 0; $j < $answers_nb_sections; $j++) {
            for ($k = 0; $k < $answers_nb_questons_per_section; $k++) {
                $current_asnwers = $current_asnwers . "s" . strval($j) . "q" . strval($k) . "a" . $answers[mt_rand(0, count($answers) - 1)] . "w" . $weights[mt_rand(0, count($weights) - 1)] . ",";
            }
        }
        //Remove last ","
        $current_asnwers = substr($current_asnwers, 0, -1);

        return $current_asnwers;
    }

    public function getDataFromString($data, $valA, $valB = null) {
        $posA = strrpos($data, $valA) + 1;

        if ($valB != null) {
            $posB = strrpos($data, $valB);
            return substr($data, $posA, $posB - $posA);
        } else {
            return substr($data, $posA);
        }
    }

    public function GetScoreFromAnswers($answers) {
        $resultRawData = explode(",", $answers);
        $score_string = "";
        $section = "0";
        for ($i = 0; $i < count($resultRawData); $i++) {
            $newSection = $this->getDataFromString($resultRawData[$i], "s", "q");
            $weight = $this->getDataFromString($resultRawData[$i], "w");

            if ($newSection != $section) {
                //Prdevent score from going under 0
                if ($score < 0)
                    $score = 0;
                $score_string = $score_string . "s" . $section . "r" . $score . "m6,";
                $section = $newSection;

                //reset score
                $score = 0;
            }
            $score += (int) $weight;
        }
        //Add last result
        if ($score < 0)
            $score = 0;
        $score_string = $score_string . "s" . $section . "r" . $score . "m6";

        return $score_string;
    }

}
