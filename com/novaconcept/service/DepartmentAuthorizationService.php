<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\transient\Permission;

class DepartmentAuthorizationService extends AbstractCoreService {

    public function edit() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments')
                ->addRequired('can_manage_users');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);

        $departmentAuthorizationData = $this->request->getPostData();
        $departmentAuthorization = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\DepartmentAuthorization', $this->request->getPathParamByName('id'));
        if ($departmentAuthorization == NULL) {
            $this->securityLog('department_authorization_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }
        $departmentInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\DepartmentInfo', $this->request->getPostData()->departmentId);
        if ($departmentInfo == NULL) {
            $this->securityLog('department_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        if ($departmentInfo->getDescription() != 'IS_AGENCY') {
            $this->securityLog('unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserDepartment = $departmentAuthorization->getUserInfo()->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoDepartment != $departmentInfo->getParent()->getParent() || $corpoDepartment != $corpoUserDepartment) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserDepartment = $departmentAuthorization->getUserInfo()->getDepartmentInfoCollection()->first()->getParent();
            if ($groupDepartment != $departmentInfo->getParent() || $groupDepartment != $groupUserDepartment) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
            if ($departmentAuthorization->getUserInfo()->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        $conflictResult = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\DepartmentAuthorization')
                ->createQueryBuilder('u')
                ->where('u.userInfo = :userInfo')
                ->andWhere('u.departmentInfo = :departmentInfo')
                ->setParameter("userInfo", $departmentAuthorization->getUserInfo())
                ->setParameter("departmentInfo", $departmentInfo)
                ->getQuery()
                ->getOneOrNullResult();
        if ($conflictResult != NULL) {
            $this->securityLog('department_authorization_already_exists');
            $this->response->setResponseStatus(409)
                    ->build();
            return;
        }

        $departmentAuthorization->setDepartmentInfo($departmentInfo);
        $this->bootstrap->getEntityManager()->merge($departmentAuthorization);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($departmentAuthorization->getData())
                ->build();
    }

}
