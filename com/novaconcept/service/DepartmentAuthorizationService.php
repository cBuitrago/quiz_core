<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\utility\Constants;

class DepartmentAuthorizationService extends AbstractCoreService {

    public function edit() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::ACCOUNT_INFO_REP, $accountId);

        $departmentAuthorizationData = $this->request->getPostData();
        $departmentAuthorization = $this->bootstrap->getEntityManager()
                ->find(Constants::DEPARTMENT_AUTHORIZATION_REP, $this->request->getPathParamByName(Constants::ID));
        if ($departmentAuthorization == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }
        $departmentInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::DEPARTMENT_INFO_REP, $this->request->getPostData()->departmentId);
        if ($departmentInfo == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        if ($departmentInfo->getDescription() != Constants::DESC_AGENCY) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserDepartment = $departmentAuthorization->getUserInfo()->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoDepartment != $departmentInfo->getParent()->getParent() || $corpoDepartment != $corpoUserDepartment) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserDepartment = $departmentAuthorization->getUserInfo()->getDepartmentInfoCollection()->first()->getParent();
            if ($groupDepartment != $departmentInfo->getParent() || $groupDepartment != $groupUserDepartment) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
            if ($departmentAuthorization->getUserInfo()->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        $conflictResult = $this->bootstrap->getEntityManager()
                ->getRepository(Constants::DEPARTMENT_AUTHORIZATION_REP)
                ->createQueryBuilder('u')
                ->where('u.userInfo = :userInfo')
                ->andWhere('u.departmentInfo = :departmentInfo')
                ->setParameter("userInfo", $departmentAuthorization->getUserInfo())
                ->setParameter("departmentInfo", $departmentInfo)
                ->getQuery()
                ->getOneOrNullResult();
        if ($conflictResult != NULL) {
            $this->securityLog(Constants::CONFLICT_STR);
            $this->response->setResponseStatus(Constants::CONFLICT)
                    ->build();
            return;
        }

        $departmentAuthorization->setDepartmentInfo($departmentInfo);
        $this->bootstrap->getEntityManager()->merge($departmentAuthorization);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($departmentAuthorization->getData())
                ->build();
    }

}
