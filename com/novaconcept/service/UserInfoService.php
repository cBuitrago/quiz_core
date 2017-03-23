<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentAuthorization;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\UserAuthentication;
use com\novaconcept\entity\UserAuthorization;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\utility\Constants;
use stdClass;

class UserInfoService extends AbstractCoreService {

    public function add() {
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
        
        $this->request->getPostData()->username = trim($this->request->getPostData()->username);
        $this->request->getPostData()->password = trim($this->request->getPostData()->password);
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::ACCOUNT_INFO_REP, $accountId);
        $result = $this->bootstrap->getEntityManager()
                ->getRepository(Constants::USER_INFO_REP)
                ->createQueryBuilder('u')
                ->where('u.username = :username')
                ->setParameter("username", $this->request->getPostData()->username)
                ->getQuery()
                ->getOneOrNullResult();

        if ($result != NULL) {
            $this->securityLog(Constants::CONFLICT_STR);
            $this->response->setResponseStatus(Constants::CONFLICT)
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
                ->find(Constants::USER_PERMISSION_REP, Constants::USER_PERMISSION_ID);
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

        $this->securityLog(Constants::CREATED);
        $this->response->setResponseStatus(Constants::CREATED)
                ->setResponseData($data)
                ->build();
    }

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

        $userInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::USER_INFO_REP, $this->request->getPathParamByName(Constants::ID));
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

        $userPermission = new Permission();
        $userPermission->addRequired(Constants::USER);
        if (($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE ||
                $userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE)) ||
                ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                $userInfo->validatePermissions($userPermission, $accountId) === FALSE)) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoUserInfo != $corpoUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupUserInfo != $groupUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyUserInfo = $userInfo->getDepartmentInfoCollection()->first();
            $agencyUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyUserInfo != $agencyUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        $postData = $this->request->getPostData();
        if (empty($postData->username) === FALSE) {
            $userInfoConflict = $this->bootstrap->getEntityManager()
                    ->getRepository(Constants::USER_INFO_REP)
                    ->createQueryBuilder('u')
                    ->where('u.username = :username')
                    ->setParameter("username", $postData->username)
                    ->getQuery()
                    ->getOneOrNullResult();
            if ($userInfoConflict != NULL && $userInfoConflict->getId() != $userInfo->getId()) {
                $this->securityLog(Constants::CONFLICT_STR);
                $this->response->setResponseStatus(Constants::CONFLICT)
                        ->build();
                return;
            }
            $userInfo->mergePostData($this->request->getPostData());
        }

        if (isset($postData->userAuthentication)) {
            $userInfo->getUserAuthentication()->mergePostData($postData->userAuthentication);
        }

        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($userInfo->getData())
                ->build();
    }

    public function getById() {
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
        $userInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::USER_INFO_REP, $this->request->getPathParamByName(Constants::ID));
        if ($userInfo == NULL) {
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

        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired(Constants::AGENCY);
        $userUserPermission = new Permission();
        $userUserPermission->addRequired(Constants::USER);

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoUserInfo != $corpoUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupUserInfo != $groupUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyUserInfo = $userInfo->getDepartmentInfoCollection()->first();
            $agencyUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyUserInfo != $agencyUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userUserPermission, $accountId) === TRUE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $includes = $this->service->validateInclude(explode(",", $this->request->getQueryParam("includes")));

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($userInfo->getData($includes))
                ->build();
    }

    public function delete() {
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
        $userInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::USER_INFO_REP, $this->request->getPathParamByName(Constants::ID));
        if ($userInfo == NULL) {
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

        $userPermission = new Permission();
        $userPermission->addRequired(Constants::USER);
        if (($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE ||
                $userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE)) ||
                ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                $userInfo->validatePermissions($userPermission, $accountId) === FALSE)) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            $corpoUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            if ($corpoUserInfo != $corpoUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupUserInfo = $userInfo->getDepartmentInfoCollection()->first()->getParent();
            $groupUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            if ($groupUserInfo != $groupUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyUserInfo = $userInfo->getDepartmentInfoCollection()->first();
            $agencyUserInfoSelf = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyUserInfo != $agencyUserInfoSelf) {
                $this->securityLog(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
        }

        $this->bootstrap->getEntityManager()->remove($userInfo);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->build();
    }

    public function getSelf() {
        $clientPermission = new Permission();
        $userPermission = new Permission();
        if ($this->isAuthenticated($clientPermission, $userPermission, NULL) === FALSE)
            return;

        $includes = $this->service->validateInclude(explode(",", $this->request->getQueryParam("includes")));

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($this->userInfo->getData($includes))
                ->build();
    }

}
