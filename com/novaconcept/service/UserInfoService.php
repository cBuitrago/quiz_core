<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\UserAuthentication;
use com\novaconcept\entity\UserAuthorization;
use com\novaconcept\entity\UserInfo;
use stdClass;

class UserInfoService extends AbstractCoreService {

    public function add() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_users');
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
        
        $this->request->getPostData()->username = trim($this->request->getPostData()->username);
        $this->request->getPostData()->password = trim($this->request->getPostData()->password);
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);
        $result = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\UserInfo')
                ->createQueryBuilder('u')
                ->where('u.username = :username')
                ->setParameter("username", $this->request->getPostData()->username)
                ->getQuery()
                ->getOneOrNullResult();

        if ($result != NULL) {
            $this->securityLog('user_already_exists');
            $this->response->setResponseStatus(409)
                    ->build();
            return;
        }

        $userInfo = new UserInfo();
        $userInfo->mapPostData($this->request->getPostData());
        $this->bootstrap->getEntityManager()->persist($userInfo);
        $this->bootstrap->getEntityManager()->flush();

        $userAuthentication = new UserAuthentication();
        $userAuthentication->mapPostData($this->request->getPostData())
                ->setUserInfo($userInfo);
        $userInfo->setUserAuthentication($userAuthentication);
        $this->bootstrap->getEntityManager()->persist($userAuthentication);
        $this->bootstrap->getEntityManager()->flush();

        $userAccount = new UserAccount();
        $userAccount->setAccountInfo($accountInfo)
                ->setUserInfo($userInfo);
        $this->bootstrap->getEntityManager()->persist($userAccount);
        $this->bootstrap->getEntityManager()->flush();

        $userPermission = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserPermission', 17);
        $userAuthorization = new UserAuthorization();
        $userAuthorization->setAccountInfo($accountInfo)
                ->setUserInfo($userInfo)
                ->setUserPermission($userPermission);
        $this->bootstrap->getEntityManager()->persist($userAuthorization);

        $userDepartment = new DepartmentAuthorization();
        $userDepartment->setUserInfo($userInfo)
                ->setIsRecursive(FALSE)
                ->setDepartmentInfo($this->userInfo->getDepartmentInfoCollection()->first());
        $this->bootstrap->getEntityManager()->persist($userDepartment);
        $this->bootstrap->getEntityManager()->flush();

        $data = new stdClass();
        $data->id = $userInfo->getId();

        $this->securityLog(201);
        $this->response->setResponseStatus(201)
                ->setResponseData($data)
                ->build();
    }

    public function edit() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_users');
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

        $userInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserInfo', $this->request->getPathParamByName('id'));
        if ($userInfo == NULL) {
            $this->securityLog('user_not_found');
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

        $userPermission = new Permission();
        $userPermission->addRequired('is_user');
        if (($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE ||
                $userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE)) ||
                ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                $userInfo->validatePermissions($userPermission, $accountId) === FALSE)) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoUserInfo != $corpoUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupUserInfo != $groupUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyUserInfo = $userInfo->getDepartmentInfoCollection()->first();
            $agencyUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyUserInfo != $agencyUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        $postData = $this->request->getPostData();
        if (empty($postData->username) === FALSE) {
            $userInfoConflict = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\UserInfo')
                    ->createQueryBuilder('u')
                    ->where('u.username = :username')
                    ->setParameter("username", $postData->username)
                    ->getQuery()
                    ->getOneOrNullResult();
            if ($userInfoConflict != NULL && $userInfoConflict->getId() != $userInfo->getId()) {
                $this->securityLog('user_already_exists');
                $this->response->setResponseStatus(409)
                        ->build();
                return;
            }
            $userInfo->mergePostData($this->request->getPostData());
        }

        if (isset($postData->userAuthentication)) {
            $userInfo->getUserAuthentication()->mergePostData($postData->userAuthentication);
        }

        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($userInfo->getData())
                ->build();
    }

    public function getById() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_users');
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
        $userInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserInfo', $this->request->getPathParamByName('id'));
        if ($userInfo == NULL) {
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

        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired('is_agency_admin');
        $userUserPermission = new Permission();
        $userUserPermission->addRequired('is_user');

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoUserInfo != $corpoUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupUserInfo != $groupUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyUserInfo = $userInfo->getDepartmentInfoCollection()->first();
            $agencyUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyUserInfo != $agencyUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userUserPermission, $accountId) === TRUE) {
            $this->securityLog('unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $includes = $this->service->validateInclude(explode(",", $this->request->getQueryParam("includes")));

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($userInfo->getData($includes))
                ->build();
    }

    public function delete() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_users');
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
        $userInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserInfo', $this->request->getPathParamByName('id'));
        if ($userInfo == NULL) {
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

        $userPermission = new Permission();
        $userPermission->addRequired('is_user');
        if (($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE ||
                $userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE)) ||
                ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                $userInfo->validatePermissions($userPermission, $accountId) === FALSE)) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoUserInfo != $corpoUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupUserInfo != $groupUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyUserInfo = $userInfo->getDepartmentInfoCollection()->first();
            $agencyUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyUserInfo != $agencyUserInfoSelf) {
                $this->securityLog('unauthorized');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
        }

        $this->bootstrap->getEntityManager()->remove($userInfo);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->build();
    }

    public function getSelf() {
        $clientPermission = new Permission();
        $userPermission = new Permission();
        if ($this->isAuthenticated($clientPermission, $userPermission, NULL) === FALSE)
            return;

        $includes = $this->service->validateInclude(explode(",", $this->request->getQueryParam("includes")));

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($this->userInfo->getData($includes))
                ->build();
    }

}
