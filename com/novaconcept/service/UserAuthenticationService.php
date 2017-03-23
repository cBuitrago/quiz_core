<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\transient\Permission;
use com\novaconcept\utility\PasswordHash;
use com\novaconcept\utility\Constants;
use DateTime;
use stdClass;

class UserAuthenticationService extends AbstractCoreService {

    public function token() {
        $clientPermission = new Permission();
        if ($this->isAuthenticated($clientPermission) === FALSE)
            return;

        $userInfo = $this->bootstrap->getEntityManager()
                ->getRepository(Constants::USER_INFO_REP)
                ->findOneBy(array('username' => $this->request->getPostData()->username));
        if ($userInfo == NULL) {
            $this->securityEvent(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        $this->userInfo = $userInfo;
        $this->response->setUser($userInfo);

        $now = new DateTime();
        $passwordLifeCycle = $this->userInfo->getAccountInfoCollection()->first()->getAccountConfig()->getPasswordLifeCycle();
        $loginAttemptLimit = $this->userInfo->getAccountInfoCollection()->first()->getAccountConfig()->getLoginAttemptLimit();
        foreach ($this->userInfo->getAccountInfoCollection() as $account) {
            if ($account->getAccountConfig()->getPasswordLifeCycle() < $passwordLifeCycle)
                $passwordLifeCycle = $account->getAccountConfig()->getPasswordLifeCycle();

            if ($account->getAccountConfig()->getLoginAttemptLimit() < $loginAttemptLimit)
                $loginAttemptLimit = $account->getAccountConfig()->getLoginAttemptLimit();
        }

        if ($now->getTimestamp() - $userInfo->getUserAuthentication()->getChangedOn()->getTimestamp() > $passwordLifeCycle) {
            $this->securityEvent(Constants::UNAUTHENTICATED_STR);
            $this->response->setResponseStatus(Constants::UNAUTHORIZED)
                    ->build();
            return;
        }

        if ($userInfo->getUserAuthentication()->getAttemptFail() > $loginAttemptLimit || $userInfo->getUserAuthentication()->getIsActive() == FALSE) {
            $this->securityEvent(Constants::UNAUTHENTICATED_STR);
            $this->response->setResponseStatus(Constants::UNAUTHORIZED)
                    ->build();
            return;
        }

        $passwordHash = new PasswordHash(8, TRUE);
        if (!$passwordHash->CheckPassword($this->request->getPostData()->password, $userInfo->getUserAuthentication()->getPassword())) {
            $userInfo->getUserAuthentication()->addAttemptFail();
            $this->bootstrap->getEntityManager()->merge($userInfo);
            $this->bootstrap->getEntityManager()->flush();
            $this->securityEvent(Constants::UNAUTHENTICATED_STR);
            $this->response->setResponseStatus(Constants::UNAUTHORIZED_STR)
                    ->build();
            return;
        }

        $includes = $this->service->validateInclude(explode(",", $this->request->getQueryParam("includes")));
        $data = new stdClass();
        $data->token = $userInfo->getUserAuthentication()->getPublicKey() . $userInfo->getUserAuthentication()->generatePrivateKey();
        $data->userInfo = $userInfo->getData($includes);

        $userInfo->getUserAuthentication()->setAttemptFail(0);
        $this->bootstrap->getEntityManager()->merge($userInfo);
        $this->bootstrap->getEntityManager()->flush();

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
        $userAuthentication = $this->bootstrap->getEntityManager()
                ->find(Constants::USER_AUTHENTICATION_REP, $this->request->getPathParamByName(Constants::ID));
        if ($userAuthentication == NULL) {
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }
        if ($userAuthentication->getUserInfo()->validateAccount($accountInfo) === FALSE) {
            $this->securityEvent(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $userPermission = new Permission();
        $userPermission->addRequired(Constants::USER);
        if (($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($userAuthentication->getUserInfo()->validatePermissions($userGroupPermission, $accountId) === TRUE ||
                $userAuthentication->getUserInfo()->validatePermissions($userGroupPermission, $accountId) === TRUE)) ||
                ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                $userAuthentication->getUserInfo()->validatePermissions($userPermission, $accountId) === FALSE)) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }


        $userAuthentication->mergePostData($this->request->getPostData());
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($userAuthentication->getData())
                ->build();
    }

    public function editSelf() {
        $clientPermission = new Permission();
        $userPermission = new Permission();
        if ($this->isAuthenticated($clientPermission, $userPermission, NULL) === FALSE)
            return;

        $this->userInfo->getUserAuthentication()->mergePostData($this->request->getPostData());
        $this->userInfo->mergePostData($this->request->getPostData());
        $this->bootstrap->getEntityManager()->merge($this->userInfo);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($this->userInfo->getData())
                ->build();
    }

}
