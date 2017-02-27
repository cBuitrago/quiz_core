<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAuthorization;

class UserAuthorizationService extends AbstractCoreService {

    public function edit() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_users')
                ->addRequired('can_manage_user_permissions');
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

        $userAuthorization = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserAuthorization', $this->request->getPathParamByName('id'));
        if ($userAuthorization == NULL) {
            $this->securityLog('user_authorization_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }
        $permissionUser = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserPermission', $this->request->getPostData()->userPermission);
        if ($permissionUser == NULL) {
            $this->securityLog('permission_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        $userInfo = $userAuthorization->getUserInfo();
        if ($userInfo == NULL) {
            $this->securityLog('user_info_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        if ($userInfo->validateAccount($accountInfo) === FALSE) {
            $this->securityEvent('cross_account');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $conflictResult = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\UserAuthorization')
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
            $this->securityLog('user_permission_already_exists');
            $this->response->setResponseStatus(409)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserDepartment = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoDepartment != $corpoUserDepartment || $userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserDepartment = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupDepartment != $groupUserDepartment ||
                    $userAuthorization->getUserPermission()->getName() == 'is_corpo_admin' ||
                    $permissionUser->getName() == 'is_corpo_admin' ||
                    $userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyDepartment = $this->userInfo->getDepartmentInfoCollection()->first();
            $agencyUserDepartment = $userInfo->getDepartmentInfoCollection()->first();
            if ($agencyDepartment != $agencyUserDepartment ||
                    $userAuthorization->getUserPermission()->getName() == 'is_corpo_admin' ||
                    $permissionUser->getName() == 'is_corpo_admin' ||
                    $userAuthorization->getUserPermission()->getName() == 'is_group_admin' ||
                    $permissionUser->getName() == 'is_group_admin' ||
                    $userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        $userAuthorization->setUserPermission($permissionUser);
        $this->bootstrap->getEntityManager()->merge($userAuthorization);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($userAuthorization->getData())
                ->build();
    }

}
