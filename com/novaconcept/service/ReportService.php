<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\transient\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use com\novaconcept\entity\DepartmentInfo;
use stdClass;

class ReportService extends AbstractCoreService {

    public function __construct($request, $bootstrap) {
        parent::__construct($request, $bootstrap);
    }

    public $data = [];

    public function report() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userAuthorization = new Permission();
        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->isAuthenticated($clientAuthorization, $userAuthorization, $accountId) === FALSE) {
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);

        $this->data = [];
        $this->data[0] = [];
        $this->data[1] = [];

        $godAuthorization = new Permission();
        $godAuthorization->addRequired('is_god');
        $corpoAuthorization = new Permission();
        $corpoAuthorization->addRequired('is_corpo_admin');
        $groupAuthorization = new Permission();
        $groupAuthorization->addRequired('is_group_admin');
        $agencyAuthorization = new Permission();
        $agencyAuthorization->addRequired('is_agency_admin');

        if ($this->userInfo->validatePermissions($godAuthorization, $accountId) !== FALSE) {
            foreach ($this->userInfo->getDepartmentInfoCollection() as $isDepartmentGod) {
                if ($isDepartmentGod->getDescription() == 'IS_GOD') {
                    $godDepartment = $isDepartmentGod;
                }
            }
            foreach ($godDepartment->getChildrenCollection() as $departmentInfo) {
                if ($departmentInfo->getChildrenCollection()->count() > 0) {
                    $info = $this->treeBuilder($departmentInfo->getChildrenCollection());
                }
            }
            $quizArray = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\Quiz')
                    ->findAll();
            foreach ($quizArray as $quiz) {
                $this->data[1][] = $quiz->getData();
            }
        } else if ($this->userInfo->validatePermissions($corpoAuthorization, $accountId) !== FALSE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();

            foreach ($corpoDepartment->getChildrenCollection() as $departmentInfo) {
                if ($departmentInfo->getChildrenCollection()->count() > 0) {
                    $info = $this->treeBuilder($departmentInfo->getChildrenCollection());
                }
            }
            $quizArray = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\Quiz')
                    ->findAll();
            foreach ($quizArray as $quiz) {
                $this->data[1][] = $quiz->getData();
            }
        } else if ($this->userInfo->validatePermissions($groupAuthorization, $accountId) !== FALSE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            foreach ($groupDepartment->getChildrenCollection() as $departmentInfo) {
                foreach ($departmentInfo->getDepartmentAuthorizationCollection() as $department) {
                    if ($department->getUserInfo()->getQuizResultsCollection()->count() > 0) {
                        foreach ($department->getUserInfo()->getQuizResultsCollection() as $quizResult) {
                            $quizResults = $quizResult->getData();
                            $quizResults[] = $quizResult->getQuizID()->getQuizID();
                            $quizResults[] = $quizResult->getUserID()->getUserName();
                            $quizResults[] = $departmentInfo->getParent()->getParent()->getId();
                            $quizResults[] = $departmentInfo->getParent()->getParent()->getName();
                            $quizResults[] = $departmentInfo->getParent()->getId();
                            $quizResults[] = $departmentInfo->getParent()->getName();
                            $quizResults[] = $departmentInfo->getId();
                            $quizResults[] = $departmentInfo->getName();
                            $quizResults[] = $quizResult->getProgressId()->getFra();
                            $this->data[0][] = $quizResults;
                        }
                    }
                }
            }
            $quizArray = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\Quiz')
                    ->findAll();
            foreach ($quizArray as $quiz) {
                $this->data[1][] = $quiz->getData();
            }
        } else if ($this->userInfo->validatePermissions($agencyAuthorization, $accountId) !== FALSE) {
            $agencyDepartment = $this->userInfo->getDepartmentInfoCollection()->first();
            foreach ($agencyDepartment->getDepartmentAuthorizationCollection() as $department) {
                if ($department->getUserInfo()->getQuizResultsCollection()->count() > 0) {
                    foreach ($department->getUserInfo()->getQuizResultsCollection() as $quizResult) {
                        $quizResults = $quizResult->getData();
                        $quizResults[] = $quizResult->getQuizID()->getQuizID();
                        $quizResults[] = $quizResult->getUserID()->getUserName();
                        $quizResults[] = $departmentInfo->getParent()->getParent()->getId();
                        $quizResults[] = $departmentInfo->getParent()->getParent()->getName();
                        $quizResults[] = $departmentInfo->getParent()->getId();
                        $quizResults[] = $departmentInfo->getParent()->getName();
                        $quizResults[] = $departmentInfo->getId();
                        $quizResults[] = $departmentInfo->getName();
                        $quizResults[] = $quizResult->getProgressId()->getFra();
                        $this->data[0][] = $quizResults;
                    }
                }
            }
            $quizArray = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\Quiz')
                    ->findAll();
            foreach ($quizArray as $quiz) {
                $this->data[1][] = $quiz->getData();
            }
        }

        $progressInfo = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\ProgressInfo')
                ->findAll();
        $this->data[2] = [];
        foreach ($progressInfo as $progress) {
            $this->data[2][] = $progress->getData();
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($this->data)
                ->build();
    }

    public function reportSelf() {
        $clientAuthorization = new Permission();
        $clientAuthorization->addRequired('is_god');
        $userAuthorization = new Permission();
        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->isAuthenticated($clientAuthorization, $userAuthorization, $accountId) === FALSE) {
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);

        $this->data = [];
        $this->data[0] = [];
        $this->data[1] = [];

        $godAuthorization = new Permission();
        $godAuthorization->addRequired('is_god');
        $corpoAuthorization = new Permission();
        $corpoAuthorization->addRequired('is_corpo_admin');
        $groupAuthorization = new Permission();
        $groupAuthorization->addRequired('is_group_admin');
        $agencyAuthorization = new Permission();
        $agencyAuthorization->addRequired('is_agency_admin');
        $userAuthorization = new Permission();
        $userAuthorization->addRequired('is_user');

        if ($this->userInfo->validatePermissions($corpoAuthorization, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($groupAuthorization, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($agencyAuthorization, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userAuthorization, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
       
        $departmentInfo = $this->userInfo->getDepartmentInfoCollection()->first();
        foreach ($this->userInfo->getQuizResultsCollection() as $quizResult) {
            if ($quizResult->getProgressId()->getId() == 3 && 
                   $quizResult->getQuizID()->getId() ==  $this->request->getPathParamByName('id')) {
                $quizResults = $quizResult->getData();
                $quizResults[] = $quizResult->getQuizID()->getQuizID();
                $quizResults[] = $quizResult->getUserID()->getUserName();
                $quizResults[] = $departmentInfo->getParent()->getParent()->getId();
                $quizResults[] = $departmentInfo->getParent()->getParent()->getName();
                $quizResults[] = $departmentInfo->getParent()->getId();
                $quizResults[] = $departmentInfo->getParent()->getName();
                $quizResults[] = $departmentInfo->getId();
                $quizResults[] = $departmentInfo->getName();
                $quizResults[] = $quizResult->getProgressId()->getFra();
                $this->data[0][] = $quizResults;
                $this->data[2][] = $quizResult->getQuizID()->getData();
            }
            if ($quizResult->getProgressId()->getId() == 3) {
                $quiz_result = [];
                $quiz_result['USER_ID'] = $this->userInfo->getId();
                $quiz_result['ANSWERS'] = $quizResult->getAnswers();
                $quiz_result['QUIZ_SCORE'] = $quizResult->getQuizScore();
                $quiz_result['QUIZ_ID'] = $quizResult->getQuizID()->getId();
                $quizGroupQuizList = $this->bootstrap->getEntityManager()
                        ->getRepository('com\novaconcept\entity\QuizGroupQuizList')
                        ->findOneBy(array('quizId' => $quizResult->getQuizID()->getId()));
                $quiz_result['QUIZ_GROUP_ID'] = $quizGroupQuizList->getQuizGroupID();
                $quiz_result['QUIZ_ORDER'] = $quizGroupQuizList->getOrderNB();
                $quiz_result['QUIZ_NAME'] = $quizResult->getQuizID()->getQuizID();
                $this->data[1][] = $quiz_result;
            }
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($this->data)
                ->build();
    }

    private function treeBuilder($departmentInfoCollection) {
        $dataBuilder = [];
        foreach ($departmentInfoCollection as $departmentInfo) {
            if ($departmentInfo->getChildrenCollection()->count() > 0) {
                $info = $this->treeBuilder($departmentInfo->getChildrenCollection());
                if (!empty($info))
                    array_push($dataBuilder, $info);
            }
            else if ($departmentInfo->getChildrenCollection()->count() == 0 && $departmentInfo->getDescription() == 'IS_AGENCY') {
                foreach ($departmentInfo->getDepartmentAuthorizationCollection() as $department) {
                    if ($department->getUserInfo()->getQuizResultsCollection()->count() > 0) {
                        foreach ($department->getUserInfo()->getQuizResultsCollection() as $quizResult) {
                            $quizResults = $quizResult->getData();
                            $quizResults[] = $quizResult->getQuizID()->getQuizID();
                            $quizResults[] = $quizResult->getUserID()->getUserName();
                            $quizResults[] = $departmentInfo->getParent()->getParent()->getId();
                            $quizResults[] = $departmentInfo->getParent()->getParent()->getName();
                            $quizResults[] = $departmentInfo->getParent()->getId();
                            $quizResults[] = $departmentInfo->getParent()->getName();
                            $quizResults[] = $departmentInfo->getId();
                            $quizResults[] = $departmentInfo->getName();
                            $quizResults[] = $quizResult->getProgressId()->getFra();
                            $this->data[0][] = $quizResults;
                        }
                    }
                }
            }
        }
        return $dataBuilder;
    }

    /* $quizResults = $quizResult->getData();
      $quizResults['QUIZ_NAME'] = $quizResult->getQuizID()->getQuizID();
      $quizResults['USER_NAME'] = $quizResult->getUserID()->getUserName();
      $quizResults['CORPORATE_ID'] = $departmentInfo->getParent()->getParent()->getId();
      $quizResults['CORPORATE_NAME'] = $departmentInfo->getParent()->getParent()->getName();
      $quizResults['GROUP_ID'] = $departmentInfo->getParent()->getId();
      $quizResults['GROUP_NAME'] = $departmentInfo->getParent()->getName();
      $quizResults['AGENCY_ID'] = $departmentInfo->getId();
      $quizResults['AGENCY_NAME'] = $departmentInfo->getName();
      $quizResults['PROGRESS_NAME'] = $quizResult->getProgressId()->getFra();
      array_push($this->data[0], $quizResults); */
}
