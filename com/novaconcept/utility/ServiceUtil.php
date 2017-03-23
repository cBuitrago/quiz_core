<?php

namespace com\novaconcept\utility;

use com\novaconcept\entity\AccountInfo;
use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\ClientAccount;
use com\novaconcept\entity\UserAccount;
use com\novaconcept\entity\transient\Authorization;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\utility\ApiConfig;
use com\novaconcept\utility\Constants;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

class ServiceUtil {

    /** @var EntityManager */
    private $entityManager;

    /**
     * 
     * @param EntityManager $entityManager
     */
    function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Authorization $authorization
     * @param ClientInfo $clientInfo
     * @param UserInfo $userInfo
     */
    public function authenticateDelay($authorization, $clientInfo, $userInfo) {
        if ($authorization->getDateDiff() < - $clientInfo->getAccountInfo()->getAccountConfig()->getRequestLifeCycle() || 
                $authorization->getDateDiff() > $clientInfo->getAccountInfo()->getAccountConfig()->getRequestLifeCycle())
            $authorization->failed(Constants::FORBIDDEN, Constants::CALL_EXPIRED);
    }

    /**
     * @param Authorization $authorization
     * @param string $httpMethod
     */
    public function authenticateReplay($authorization, $httpMethod) {
        $nonceLifeCycle = new DateTime();
        $nonceLifeCycle->setTimestamp($authorization->getNow()->getTimestamp() - ApiConfig::getData()->settings->nonceLifeCycle);

        $securityReplay = $this->entityManager
                ->getRepository(Constants::SECURITY_REPLAY_REP)
                ->createQueryBuilder('u')
                ->where('u.endpoint = :endpoint')
                ->andWhere('u.httpMethod = :httpMethod')
                ->andWhere('u.signature = :signature')
                ->andWhere('u.nonce = :nonce')
                ->andWhere('u.createdOn BETWEEN :lifeCycle AND :now')
                ->setParameter("endpoint", $authorization->getUrl())
                ->setParameter("httpMethod", $httpMethod)
                ->setParameter("signature", $authorization->getSignature())
                ->setParameter("nonce", $authorization->getNonce())
                ->setParameter("lifeCycle", $nonceLifeCycle->format('Y-m-d H:i:s'))
                ->setParameter("now", $authorization->getNow()->format('Y-m-d H:i:s'))
                ->getQuery()
                ->getOneOrNullResult();

        if ($securityReplay !== NULL)
            $authorization->failed(Constants::FORBIDDEN, Constants::REPLAY);
    }

    /**
     * @param Authorization $authorization
     * @return ClientInfo
     */
    public function authenticateClient($authorization) {
        $clientAuthentication = $this->entityManager
                ->getRepository(Constants::CLIENT_AUTHENTICATION_REP)
                ->createQueryBuilder('u')
                ->where('u.publicKey = :publicKey')
                ->setParameter("publicKey", $authorization->getClientPublic())
                ->getQuery()
                ->getOneOrNullResult();

        if ($clientAuthentication !== NULL && $clientAuthentication->validateAuthorization($authorization))
            return $clientAuthentication->getClientInfo();

        $authorization->failed(Constants::UNAUTHORIZED, Constants::CLIENT_MISSING);
        return NULL;
    }

    /**
     * @param Authorization $authorization
     * @return UserInfo
     */
    public function authenticateUser($authorization) {
        $userAuthorization = $this->entityManager
                ->getRepository(Constants::USER_AUTHENTICATION_REP)
                ->createQueryBuilder('u')
                ->where('u.publicKey = :publicKey')
                ->setParameter('publicKey', $authorization->getUserPublic())
                ->getQuery()
                ->getOneOrNullResult();

        if ($userAuthorization !== NULL && $userAuthorization->validateAuthorization($authorization))
            return $userAuthorization->getUserInfo();

        return NULL;
    }

    /**
     * 
     * @param Authorization $authorization
     * @param ClientInfo $clientInfo
     * @param Permission $clientPermission
     * @param UserInfo $userInfo
     * @param Permission $userPermmission
     * @return boolean
     */
    public function isAuthenticated($authorization, $clientInfo = NULL, $clientPermission = NULL, $userInfo = NULL, $userPermmission = NULL, $accountId = NULL) {
        if ($accountId != NULL) {
            $queryBuilder = $this->entityManager->createQueryBuilder('a');
            $queryBuilder
                    ->select('a')
                    ->from(Constants::ACCOUNT_INFO_REP, 'a')
                    ->leftJoin(Constants::CLIENT_ACCOUNT_REP, 'c', Join::WITH, 'a.id = c.accountInfo')
                    ->where('a.isActive = 1')
                    ->andWhere('a.id = :accountInfoId')
                    ->andWhere('c.clientInfo = :clientInfoId')
                    ->setParameter('accountInfoId', $accountId)
                    ->setParameter('clientInfoId', $clientInfo->getId());

            if ($userInfo != NULL) {
                $queryBuilder
                        ->leftJoin(Constants::USER_ACCOUNT_REP, 'u', Join::WITH, 'a.id = u.accountInfo')
                        ->andWhere('u.userInfo = :userInfoId')
                        ->setParameter('userInfoId', $userInfo->getId());
            }

            $accountInfo = $queryBuilder->getQuery()->getOneOrNullResult();

            if ($accountInfo == NULL) {
                $authorization->failed(Constants::FORBIDDEN, Constants::CROSS_ACCOUNT);
                return $authorization->getIsValid();
            }
        }

        if ($authorization->getDateDiff() < - ApiConfig::getData()->settings->requestLifecycle || 
                $authorization->getDateDiff() > ApiConfig::getData()->settings->requestLifecycle) {
            $authorization->failed(Constants::FORBIDDEN, Constants::REQUEST_EXPIRED);
            return $authorization->getIsValid();
        }

        if (isset($userPermmission) === TRUE && isset($userInfo) === FALSE) {
            $authorization->failed(Constants::UNAUTHORIZED, Constants::USER_MISSING);
            return $authorization->getIsValid();
        }

        if ($authorization->getHasUser() === TRUE && isset($userInfo) === FALSE) {
            $authorization->failed(Constants::UNAUTHORIZED, Constants::USER_NOT_FOUND);
            return $authorization->getIsValid();
        }

        if (isset($clientPermission) === TRUE && $clientInfo === NULL) {
            $authorization->failed(Constants::FORBIDDEN, Constants::CLIENT_NOT_AUTHORIZED);
            return $authorization->getIsValid();
        }

        if (isset($clientPermission) === TRUE && $clientInfo->validatePermissions($clientPermission, $accountId) === FALSE) {
            $authorization->failed(Constants::FORBIDDEN, Constants::CLIENT_NOT_AUTHORIZED);
            return $authorization->getIsValid();
        }

        if (isset($userPermmission) === TRUE && $userInfo->validatePermissions($userPermmission, $accountId) === FALSE) {
            $authorization->failed(Constants::FORBIDDEN, Constants::USER_NOT_AUTHORIZED);
            return $authorization->getIsValid();
        }

        return $authorization->getIsValid();
    }

    /**
     * 
     * @param Authorization $authorization
     * @param int $accountInfoId
     * @param ClientInfo $clientInfo
     * @param UserInfo $userInfo
     * @return AccountInfo
     */
    public function authenticateAccount($authorization, $accountInfoId, $clientInfo, $userInfo = NULL) {
        $queryBuilder = $this->entityManager->createQueryBuilder('a');
        $queryBuilder
                ->select('a')
                ->from(Constants::ACCOUNT_INFO_REP, 'a')
                ->leftJoin(Constants::CLIENT_ACCOUNT_REP, 'c', Join::WITH, 'a.id = c.accountInfo')
                ->where('a.isActive = 1')
                ->andWhere('a.id = :accountInfoId')
                ->andWhere('c.clientInfo = :clientInfoId')
                ->setParameter('accountInfoId', $accountInfoId)
                ->setParameter('clientInfoId', $clientInfo->getId());

        if ($userInfo != NULL) {
            $queryBuilder
                    ->leftJoin(Constants::USER_ACCOUNT_REP, 'u', Join::WITH, 'a.id = u.accountInfo')
                    ->andWhere('u.userInfo = :userInfoId')
                    ->setParameter('userInfoId', $userInfo->getId());
        }

        $accountInfo = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($accountInfo == NULL)
            $authorization->failed(Constants::FORBIDDEN, Constants::CROSS_ACCOUNT);

        return $accountInfo;
    }

    /**
     * 
     * @param array $include
     * @return array
     */
    public function validateInclude($include) {
        // TODO-LOCAL: cleaning include permissions algorithm
        return $include;
    }

}
