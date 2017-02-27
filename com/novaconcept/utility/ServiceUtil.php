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
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

class ServiceUtil
{
    /** @var EntityManager */
    private $entityManager;
    /**
     * 
     * @param EntityManager $entityManager
     */
    function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @param Authorization $authorization
     * @param ClientInfo $clientInfo
     * @param UserInfo $userInfo
     */
    public function authenticateDelay($authorization, $clientInfo, $userInfo)
    {
        if ($authorization->getDateDiff() < - $clientInfo->getAccountInfo()->getAccountConfig()->getRequestLifeCycle()
                || $authorization->getDateDiff() > $clientInfo->getAccountInfo()->getAccountConfig()->getRequestLifeCycle())
            $authorization->failed(403, 'call_expired');
    }
    
    /**
     * @param Authorization $authorization
     * @param string $httpMethod
     */
    public function authenticateReplay($authorization, $httpMethod)
    {
        $nonceLifeCycle = new DateTime();
        $nonceLifeCycle->setTimestamp($authorization->getNow()->getTimestamp() - ApiConfig::getData()->settings->nonceLifeCycle );
        
        $securityReplay = $this->entityManager
                ->getRepository('com\novaconcept\entity\SecurityReplay')
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
            $authorization->failed(403, 'replay');
    }
    
    /**
     * @param Authorization $authorization
     * @return ClientInfo
     */
    public function authenticateClient($authorization)
    {
        $clientAuthentication = $this->entityManager
                ->getRepository('com\novaconcept\entity\ClientAuthentication')
                ->createQueryBuilder('u')
                ->where('u.publicKey = :publicKey')
                ->setParameter("publicKey", $authorization->getClientPublic())
                ->getQuery()
                ->getOneOrNullResult();
        
        if ($clientAuthentication !== NULL && $clientAuthentication->validateAuthorization($authorization))
            return $clientAuthentication->getClientInfo();

        $authorization->failed(401, 'client_missing');
        return NULL;
    }

    /**
     * @param Authorization $authorization
     * @return UserInfo
     */
    public function authenticateUser($authorization)
    {
        $userAuthorization = $this->entityManager
                ->getRepository('com\novaconcept\entity\UserAuthentication')
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
    public function isAuthenticated($authorization, $clientInfo = NULL, $clientPermission = NULL, $userInfo = NULL, $userPermmission = NULL, $accountId = NULL)
    {
        if ($accountId != NULL)
        {
            $queryBuilder = $this->entityManager->createQueryBuilder('a');
            $queryBuilder
                    ->select('a')
                    ->from('com\novaconcept\entity\AccountInfo', 'a')
                    ->leftJoin('com\novaconcept\entity\ClientAccount', 'c', Join::WITH, 'a.id = c.accountInfo')
                    ->where('a.isActive = 1')
                    ->andWhere('a.id = :accountInfoId')
                    ->andWhere('c.clientInfo = :clientInfoId')
                    ->setParameter('accountInfoId', $accountId)
                    ->setParameter('clientInfoId', $clientInfo->getId());

            if ($userInfo != NULL)
            {
                $queryBuilder
                        ->leftJoin('com\novaconcept\entity\UserAccount', 'u', Join::WITH, 'a.id = u.accountInfo')
                        ->andWhere('u.userInfo = :userInfoId')
                        ->setParameter('userInfoId', $userInfo->getId());
            }

            $accountInfo = $queryBuilder->getQuery()->getOneOrNullResult();

            if ($accountInfo == NULL)
            {
                $authorization->failed(403, 'cross_account');
                return $authorization->getIsValid();
            }
        }
        
        if ($authorization->getDateDiff() < - ApiConfig::getData()->settings->requestLifecycle 
                || $authorization->getDateDiff() > ApiConfig::getData()->settings->requestLifecycle)
        {
            $authorization->failed(403, 'request_expired');
            return $authorization->getIsValid();
        }
        
        if (isset($userPermmission) === TRUE && isset($userInfo) === FALSE)
        {
            $authorization->failed(401, 'user_missing');
            return $authorization->getIsValid();
        }
        
        if ($authorization->getHasUser() === TRUE && isset($userInfo) === FALSE)
        {
            $authorization->failed(401, 'user_not_found');
            return $authorization->getIsValid();
        }
        
        if (isset($clientPermission) === TRUE && $clientInfo === NULL)
        {
            $authorization->failed(403, 'client_not_authorized');
            return $authorization->getIsValid();
        }
        
        if (isset($clientPermission) === TRUE && $clientInfo->validatePermissions($clientPermission, $accountId) === FALSE)
        {
            $authorization->failed(403, 'client_not_authorized');
            return $authorization->getIsValid();
        }
        
        if (isset($userPermmission) === TRUE && $userInfo->validatePermissions($userPermmission, $accountId) === FALSE)
        {
            $authorization->failed(403, 'user_not_authorized');
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
    public function authenticateAccount($authorization, $accountInfoId, $clientInfo, $userInfo = NULL)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder('a');
        $queryBuilder
                ->select('a')
                ->from('com\novaconcept\entity\AccountInfo', 'a')
                ->leftJoin('com\novaconcept\entity\ClientAccount', 'c', Join::WITH, 'a.id = c.accountInfo')
                ->where('a.isActive = 1')
                ->andWhere('a.id = :accountInfoId')
                ->andWhere('c.clientInfo = :clientInfoId')
                ->setParameter('accountInfoId', $accountInfoId)
                ->setParameter('clientInfoId', $clientInfo->getId());
        
        if ($userInfo != NULL)
        {
            $queryBuilder
                    ->leftJoin('com\novaconcept\entity\UserAccount', 'u', Join::WITH, 'a.id = u.accountInfo')
                    ->andWhere('u.userInfo = :userInfoId')
                    ->setParameter('userInfoId', $userInfo->getId());
        }
     
        $accountInfo = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($accountInfo == NULL)
            $authorization->failed(403, 'cross_account');
        
        return $accountInfo;
    }
    
    /**
     * 
     * @param array $include
     * @return array
     */
    public function validateInclude($include)
    {
        // TODO-LOCAL: cleaning include permissions algorithm
        return $include;
    }
}
