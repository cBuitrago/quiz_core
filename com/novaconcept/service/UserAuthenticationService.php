<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\transient\Permission;
use com\novaconcept\utility\PasswordHash;
use DateTime;
use stdClass;

class UserAuthenticationService extends AbstractCoreService {

    public function token() {
        $clientPermission = new Permission();
        if ($this->isAuthenticated($clientPermission) === FALSE)
            return;

        $userInfo = $this->bootstrap->getEntityManager()
                ->getRepository('com\novaconcept\entity\UserInfo')
                ->findOneBy(array('username' => $this->request->getPostData()->username));
        if ($userInfo == NULL) {
            $this->securityEvent('not_found');
            $this->response->setResponseStatus(404)
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
            $this->securityEvent('login');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($userInfo->getUserAuthentication()->getAttemptFail() > $loginAttemptLimit || $userInfo->getUserAuthentication()->getIsActive() == FALSE) {
            $this->securityEvent('login');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $passwordHash = new PasswordHash(8, TRUE);
        if (!$passwordHash->CheckPassword($this->request->getPostData()->password, $userInfo->getUserAuthentication()->getPassword())) {
            $userInfo->getUserAuthentication()->addAttemptFail();
            $this->bootstrap->getEntityManager()->merge($userInfo);
            $this->bootstrap->getEntityManager()->flush();
            $this->securityEvent('login');
            $this->response->setResponseStatus(403)
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
        $userAuthentication = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\UserAuthentication', $this->request->getPathParamByName('id'));
        if ($userAuthentication == NULL) {
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }
        if ($userAuthentication->getUserInfo()->validateAccount($accountInfo) === FALSE) {
            $this->securityEvent('cross_account');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $userPermission = new Permission();
        $userPermission->addRequired('is_user');
        if (($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($userAuthentication->getUserInfo()->validatePermissions($userGroupPermission, $accountId) === TRUE ||
                $userAuthentication->getUserInfo()->validatePermissions($userGroupPermission, $accountId) === TRUE)) ||
                ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                $userAuthentication->getUserInfo()->validatePermissions($userPermission, $accountId) === FALSE)) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }


        $userAuthentication->mergePostData($this->request->getPostData());
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
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

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($this->userInfo->getData())
                ->build();
    }

}
