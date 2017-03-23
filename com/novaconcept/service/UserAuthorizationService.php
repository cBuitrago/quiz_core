<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAuthorization;
use com\novaconcept\utility\Constants;

class UserAuthorizationService extends AbstractCoreService {

    public function edit() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired(Constants::AGENCY);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userAgencyPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::ACCOUNT_INFO_REP, $accountId);

        $userAuthorization = $this->bootstrap->getEntityManager()
                ->find(Constants::USER_AUTHORIZATION_REP, $this->request->getPathParamByName(Constants::ID));
        if ($userAuthorization == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }
        $permissionUser = $this->bootstrap->getEntityManager()
                ->find(Constants::USER_PERMISSION_REP, $this->request->getPostData()->userPermission);
        if ($permissionUser == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        $userInfo = $userAuthorization->getUserInfo();
        if ($userInfo == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        if ($userInfo->validateAccount($accountInfo) === FALSE) {
            $this->securityEvent(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $conflictResult = $this->bootstrap->getEntityManager()
                ->getRepository(Constants::USER_AUTHORIZATION_REP)
                ->createQueryBuilder('u')
                ->where('u.userInfo = :userInfo')
                ->andWhere('u.userPermission = :userPermission')
                ->andWhere('u.accountInfo = :accountInfo')
                ->setParameter("userInfo", $userInfo)
                ->setParameter("userPermission", $permissionUser)
                ->setParameter("accountInfo", $accountInfo)
                ->getQuery()
                ->getOneOrNullResult();
        if ($conflictResult != NULL) {
            $this->securityLog(Constants::CONFLICT_STR);
            $this->response->setResponseStatus(Constants::CONFLICT)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserDepartment = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoDepartment != $corpoUserDepartment || $userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserDepartment = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupDepartment != $groupUserDepartment ||
                    $userAuthorization->getUserPermission()->getName() == Constants::CORPO ||
                    $permissionUser->getName() == Constants::CORPO ||
                    $userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyDepartment = $this->userInfo->getDepartmentInfoCollection()->first();
            $agencyUserDepartment = $userInfo->getDepartmentInfoCollection()->first();
            if ($agencyDepartment != $agencyUserDepartment ||
                    $userAuthorization->getUserPermission()->getName() == Constants::CORPO ||
                    $permissionUser->getName() == Constants::CORPO ||
                    $userAuthorization->getUserPermission()->getName() == Constants::GROUP ||
                    $permissionUser->getName() == Constants::GROUP ||
                    $userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        $userAuthorization->setUserPermission($permissionUser);
        $this->bootstrap->getEntityManager()->merge($userAuthorization);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($userAuthorization->getData())
                ->build();
    }

}
