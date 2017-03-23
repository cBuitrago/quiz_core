<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\transient\Permission;
use com\novaconcept\utility\Constants;

class DepartmentInfoService extends AbstractCoreService {

    public function add() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($this->request->getPostData()->description == Constants::DESC_CORPO ||
                $this->request->getPostData()->description == Constants::DESC_GROUP)) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::ACCOUNT_INFO_REP, $accountId);
        $departmentInfo = new DepartmentInfo();
        $departmentInfo->setAccountInfo($accountInfo);
        $departmentInfo->mapPostData($this->request->getPostData());

        if (isset($this->request->getPostData()->parent)) {
            $parent = $this->bootstrap->getEntityManager()
                    ->find(Constants::DEPARTMENT_INFO_REP, $this->request->getPostData()->parent);
            if ($parent == NULL) {
                $this->securityLog(Constants::NOT_FOUND_STR);
                $this->response->setResponseStatus(Constants::NOT_FOUND)
                        ->build();
                return;
            }
            if ($parent->getAccountInfo() !== $accountInfo) {
                $this->securityEvent(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
            $result = $this->bootstrap->getEntityManager()
                    ->getRepository(Constants::DEPARTMENT_INFO_REP)
                    ->createQueryBuilder('u')
                    ->where('u.accountInfo = :accountInfo')
                    ->andWhere('u.name = :name')
                    ->andWhere('u.parent = :parent')
                    ->setParameter("accountInfo", $accountInfo)
                    ->setParameter("name", $departmentInfo->getName())
                    ->setParameter("parent", $parent)
                    ->getQuery()
                    ->getOneOrNullResult();
        } else {
            $result = $this->bootstrap->getEntityManager()
                    ->getRepository(Constants::DEPARTMENT_INFO_REP)
                    ->createQueryBuilder('u')
                    ->where('u.accountInfo = :accountInfo')
                    ->andWhere('u.name = :name')
                    ->andWhere('u.parent is NULL')
                    ->setParameter("accountInfo", $accountInfo)
                    ->setParameter("name", $departmentInfo->getName())
                    ->getQuery()
                    ->getOneOrNullResult();
        }
        if ($result != NULL) {
            $this->response->setResponseStatus(Constants::CONFLICT)
                    ->build();
            return;
        }

        if (isset($parent))
            $departmentInfo->setParent($parent);

        $this->bootstrap->getEntityManager()->persist($departmentInfo);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::CREATED);
        $this->response->setResponseStatus(Constants::CREATED)
                ->setResponseData($departmentInfo->getData())
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
        $departmentInfo = $this->bootstrap->getEntityManager()
                ->find(Constants::DEPARTMENT_INFO_REP, $this->request->getPathParamByName(Constants::ID));
        if ($departmentInfo == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }

        if (($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                ($departmentInfo->getDescription() == Constants::DESC_CORPO ||
                $departmentInfo->getDescription() == Constants::DESC_GROUP)) ||
                ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                $departmentInfo->getDescription() == Constants::DESC_CORPO)) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                ($departmentInfo != $this->userInfo->getDepartmentInfoCollection()->first())) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                (($departmentInfo->getDescription() == Constants::DESC_CORPO &&
                $departmentInfo != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()) ||
                ($departmentInfo->getDescription() == Constants::DESC_AGENCY &&
                $departmentInfo->getParent() != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()))) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE &&
                (($departmentInfo->getDescription() == Constants::DESC_CORPO &&
                $departmentInfo != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent()) ||
                ($departmentInfo->getDescription() == Constants::DESC_GROUP &&
                $departmentInfo->getParent() != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent()) ||
                ($departmentInfo->getDescription() == Constants::DESC_AGENCY &&
                $departmentInfo->getParent()->getParent() != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent()))) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $name = (isset($this->request->getPostData()->name)) ? $this->request->getPostData()->name : $departmentInfo->getName();

        if (isset($this->request->getPostData()->parent)) {
            $parent = $this->bootstrap->getEntityManager()
                    ->find(Constants::DEPARTMENT_INFO_REP, $this->request->getPostData()->parent);
            if ($parent == NULL) {
                $this->securityLog(Constants::NOT_FOUND_STR);
                $this->response->setResponseStatus(Constants::NOT_FOUND)
                        ->build();
                return;
            }
            if ($parent->getAccountInfo() !== $accountInfo) {
                $this->securityEvent(Constants::UNAUTHORIZED_STR);
                $this->response->setResponseStatus(Constants::FORBIDDEN)
                        ->build();
                return;
            }
            $result = $this->bootstrap->getEntityManager()
                    ->getRepository(Constants::DEPARTMENT_INFO_REP)
                    ->createQueryBuilder('u')
                    ->where('u.accountInfo = :accountInfo')
                    ->andWhere('u.name = :name')
                    ->andWhere('u.parent = :parent')
                    ->andWhere('u.id != :id')
                    ->setParameter("accountInfo", $accountInfo)
                    ->setParameter("name", $name)
                    ->setParameter("parent", $parent)
                    ->setParameter("id", $departmentInfo)
                    ->getQuery()
                    ->getOneOrNullResult();
        } else {
            $result = $this->bootstrap->getEntityManager()
                    ->getRepository(Constants::DEPARTMENT_INFO_REP)
                    ->createQueryBuilder('u')
                    ->where('u.accountInfo = :accountInfo')
                    ->andWhere('u.name = :name')
                    ->andWhere('u.parent is NULL')
                    ->andWhere('u.id != :id')
                    ->setParameter("accountInfo", $accountInfo)
                    ->setParameter("name", $name)
                    ->setParameter("id", $departmentInfo)
                    ->getQuery()
                    ->getOneOrNullResult();
        }
        if ($result !== NULL) {
            $this->securityLog(Constants::CONFLICT_STR);
            $this->response->setResponseStatus(Constants::CONFLICT)
                    ->build();
            return;
        }

        if (isset($parent) === TRUE &&
                ($departmentInfo->getParent() === NULL ||
                $departmentInfo->getParent() !== $parent)) {
            $departmentInfo->setParent($parent);
        } else if (isset($parent) === FALSE &&
                $departmentInfo->getParent() !== NULL) {
            $departmentInfo->setParent(NULL);
        }

        $departmentInfo->mergePostData($this->request->getPostData());
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->build();
    }

    public function getByAccountInfoIdAgency() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired(Constants::AGENCY);

        $data = [];

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
                foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                    if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                        $agency = [];
                        $agency['id'] = $agencyDepartment->getId();
                        $agency['name'] = $agencyDepartment->getName();
                        $agency['parentName'] = $agencyDepartment->getParent()->getName();
                        array_push($data, $agency);
                    }
                }
            }
        }
        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                    $agency = [];
                    $agency['id'] = $agencyDepartment->getId();
                    $agency['name'] = $agencyDepartment->getName();
                    $agency['parentName'] = $agencyDepartment->getParent()->getName();
                    array_push($data, $agency);
                }
            }
        }
        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyDepartment = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                $agency = [];
                $agency['id'] = $agencyDepartment->getId();
                $agency['name'] = $agencyDepartment->getName();
                $agency['parentName'] = $agencyDepartment->getParent()->getName();
                array_push($data, $agency);
            }
        }

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($data)
                ->build();
    }

    public function corpo() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }
        $data = [];

        $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
        $data['corpo'] = $corpoDepartment->getData();
        $data['groups'] = [];
        foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
            if ($groupDepartment->getDescription() == Constants::DESC_GROUP) {
                array_push($data['groups'], $groupDepartment->getData());
            }
        }

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($data)
                ->build();
    }

    public function group() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $data = [];

        $groupDepartment = $this->bootstrap->getEntityManager()
                ->find(Constants::DEPARTMENT_INFO_REP, $this->request->getPathParamByName(Constants::ID));
        if ($groupDepartment == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }
        if ($this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent() != $groupDepartment->getParent()) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $data['group'] = $groupDepartment->getData();
        $data['agency'] = [];
        foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
            if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                array_push($data['agency'], $agencyDepartment->getData(array('parent')));
            }
        }

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($data)
                ->build();
    }

    public function agency() {
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

        $agencyDepartment = $this->bootstrap->getEntityManager()
                ->find(Constants::DEPARTMENT_INFO_REP, $this->request->getPathParamByName(Constants::ID));
        if ($agencyDepartment == NULL) {
            $this->securityLog(Constants::NOT_FOUND_STR);
            $this->response->setResponseStatus(Constants::NOT_FOUND)
                    ->build();
            return;
        }
        if ($this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent() != $agencyDepartment->getParent()->getParent()) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }

        $data = [];
        $data['group'] = $agencyDepartment->getParent()->getData();
        $data['agency'] = $agencyDepartment->getData();
        $data['users'] = [];

        foreach ($agencyDepartment->getUserInfoCollection() as $user) {
            array_push($data['users'], $user->getDataArray());
        }

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($data)
                ->build();
    }

    public function getAllUsers() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired(Constants::AGENCY);

        $data = [];

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
                foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                    if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                        foreach ($agencyDepartment->getUserInfoCollection() as $user) {
                            $userArray = [];
                            $userArray[] = $user->getId();
                            $userArray[] = $user->getName();
                            $userArray[] = $user->getFirstname();
                            $userArray[] = $user->getUsername();
                            $userArray[] = $agencyDepartment->getName();
                            $userArray[] = $agencyDepartment->getParent()->getName();
                            $userArray[] = $agencyDepartment->getParent()->getParent()->getName();
                            $data[] = $userArray;
                        }
                    }
                }
            }
        }
        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent();
            foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                    foreach ($agencyDepartment->getUserInfoCollection() as $user) {
                        $userArray = [];
                        $userArray[] = $user->getId();
                        $userArray[] = $user->getName();
                        $userArray[] = $user->getFirstname();
                        $userArray[] = $user->getUsername();
                        $userArray[] = $agencyDepartment->getName();
                        $userArray[] = $agencyDepartment->getParent()->getName();
                        $userArray[] = $agencyDepartment->getParent()->getParent()->getName();
                        $data[] = $userArray;
                    }
                }
            }
        }
        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE) {
            $agencyDepartment = $this->userInfo->getDepartmentInfoCollection()->first();
            if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                foreach ($agencyDepartment->getUserInfoCollection() as $user) {
                    $userArray = [];
                    $userArray[] = $user->getId();
                    $userArray[] = $user->getName();
                    $userArray[] = $user->getFirstname();
                    $userArray[] = $user->getUsername();
                    $userArray[] = $agencyDepartment->getName();
                    $userArray[] = $agencyDepartment->getParent()->getName();
                    $userArray[] = $agencyDepartment->getParent()->getParent()->getName();
                    $data[] = $userArray;
                }
            }
        }

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($data)
                ->build();
    }

    public function getAllAgencies() {
        $clientPermission = new Permission();
        $clientPermission->addRequired(Constants::GOD);
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired(Constants::CORPO);
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired(Constants::GROUP);

        $accountId = $this->request->getPathParamByName(Constants::ACCOUNT);
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE &&
                $this->userInfo->validatePermissions($userGroupPermission, $accountId) === FALSE) {
            $this->securityLog(Constants::UNAUTHORIZED_STR);
            $this->response->setResponseStatus(Constants::FORBIDDEN)
                    ->build();
            return;
        }
        $data = [];

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
                foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                    if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                        array_push($data, $agencyDepartment->getData());
                    }
                }
            }
        }
        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE) {
            $groupDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                if ($agencyDepartment->getDescription() == Constants::DESC_AGENCY) {
                    array_push($data, $agencyDepartment->getData());
                }
            }
        }

        $this->securityLog(Constants::OK);
        $this->response->setResponseStatus(Constants::OK)
                ->setResponseData($data)
                ->build();
    }

    private function treeBuilder($departmentInfoCollection) {
        $data = [];
        foreach ($departmentInfoCollection as $departmentInfo) {
            $info = $departmentInfo->getData();
            if ($departmentInfo->getChildrenCollection()->count() > 0) {
                $info->childrenCollection = $this->treeBuilder($departmentInfo->getChildrenCollection());
            }
            array_push($data, $info);
        }
        return $data;
    }

}