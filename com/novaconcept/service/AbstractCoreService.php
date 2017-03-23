<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\ClientInfo;
use com\novaconcept\entity\SecurityEvent;
use com\novaconcept\entity\SecurityLog;
use com\novaconcept\entity\SecurityReplay;
use com\novaconcept\entity\transient\Authorization;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\entity\UserInfo;
use com\novaconcept\utility\ServiceUtil;
use com\novaconcept\utility\Constants;
use microtime;
use stdClass;

/**
 * Description of AbstractService
 *
 * @author massimo
 */
class AbstractCoreService extends AbstractService {

    /** @var UserInfo */
    protected $userInfo;

    /** @var ClientInfo */
    protected $clientInfo;

    /** @var Authorization */
    protected $authorization;

    /** @var ServiceUtil */
    protected $service;

    /** @var Timestamp */
    private $startTime;

    function __construct($request, $bootstrap) {
        $this->startTime = microtime(TRUE);
        parent::__construct($request, $bootstrap);

        $this->authorization = new Authorization();
        $this->authorization->mapData($this->request->getUrl(), $this->request->getRequestHeader(Constants::DATE), $this->request->getRequestHeader(Constants::AUTHORIZATION));

        if ($this->authorization->getIsValid() === FALSE) {
            $this->authorization->failed(Constants::UNAUTHORIZED, Constants::UNAUTHENTICATED_STR);
            return;
        }

        $this->service = new ServiceUtil($this->bootstrap->getEntityManager());

        if ($this->authorization->getHasUser() === TRUE) {
            $this->userInfo = $this->service->authenticateUser($this->authorization);
            $this->response->setUser($this->userInfo);
        }
        $this->clientInfo = $this->service->authenticateClient($this->authorization);
        $this->response->setClient($this->clientInfo);

        $this->service->authenticateReplay($this->authorization, $this->request->getRequestMethod());
        if ($this->authorization->getIsValid() === FALSE) {
            $this->authorization->failed(Constants::UNAUTHORIZED, Constants::UNAUTHENTICATED_STR);
            return;
        }

        $securityReplay = new SecurityReplay();
        $securityReplay->setSignature($this->authorization->getSignature());
        $securityReplay->setNonce($this->authorization->getNonce());

        $securityReplay->setIpAddress($_SERVER['REMOTE_ADDR']);
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $securityReplay->setForIpAddress($_SERVER['HTTP_X_FORWARDED_FOR']);

        $securityReplay->setHttpMethod($_SERVER['REQUEST_METHOD']);
        $securityReplay->setEndpoint($this->request->getUrl());
        $this->bootstrap->getEntityManager()->persist($securityReplay);
        $this->bootstrap->getEntityManager()->flush();
    }

    /**
     * 
     * @param Permission $clientPermission
     * @param Permission $userPermmission
     * @return boolean
     */
    protected function isAuthenticated($clientPermission = NULL, $userPermmission = NULL, $accountId = NULL) {
        $result = ($this->service !== NULL) ?
                $this->service->isAuthenticated($this->authorization, $this->clientInfo, $clientPermission, $this->userInfo, $userPermmission, $accountId) :
                FALSE;

        if ($result === FALSE) {
            $this->securityEvent($this->authorization->getEventName());
            $this->response->setResponseStatus($this->authorization->getStatusCode())
                    ->setResponseData($this->authorization->getEventName())
                    ->build();
        }
        return $result;
    }

    /**
     * 
     * @param string $eventName
     */
    protected function securityEvent($eventName) {
        $securityEvent = new SecurityEvent;
        $requestData = new stdClass;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $requestData->forIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $requestData->forIpAddress = NULL;
        }

        $requestData->ipAddress = $_SERVER['REMOTE_ADDR'];
        $requestData->httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestData->endpoint = $_SERVER['REQUEST_URI'];
        $requestData->eventName = $eventName;
        $securityEvent->mapPostData($requestData);
        $this->bootstrap->getEntityManager()->persist($securityEvent);
        $this->bootstrap->getEntityManager()->flush();
    }

    /**
     * 
     * @param string $response
     */
    protected function securityLog($response) {
        $securityLog = new SecurityLog();
        $securityLog->setClientInfo($this->clientInfo);
        $securityLog->setUserInfo(($this->userInfo) ? $this->userInfo : NULL);

        $requestData = new stdClass;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $requestData->forIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $requestData->forIpAddress = NULL;
        }

        $requestData->ipAddress = $_SERVER['REMOTE_ADDR'];
        $requestData->httpMethod = $_SERVER['REQUEST_METHOD'];
        $requestData->endpoint = $_SERVER['REQUEST_URI'];
        $requestData->response = $response;
        $requestData->executionTime = microtime(TRUE) - $this->startTime;
        $securityLog->mapPostData($requestData);
        $this->bootstrap->getEntityManager()->persist($securityLog);
        $this->bootstrap->getEntityManager()->flush();
    }

}