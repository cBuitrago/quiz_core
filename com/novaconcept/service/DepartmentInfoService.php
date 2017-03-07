<?php

namespace com\novaconcept\service;

use com\novaconcept\entity\DepartmentInfo;
use com\novaconcept\entity\transient\Permission;

class DepartmentInfoService extends AbstractCoreService {

    public function add() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
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

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                ($this->request->getPostData()->description == "IS_CORPO" ||
                $this->request->getPostData()->description == "IS_GROUP")) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $accountInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\AccountInfo', $accountId);
        $departmentInfo = new DepartmentInfo();
        $departmentInfo->setAccountInfo($accountInfo);
        $departmentInfo->mapPostData($this->request->getPostData());

        if (isset($this->request->getPostData()->parent)) {
            $parent = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\DepartmentInfo', $this->request->getPostData()->parent);
            if ($parent == NULL) {
                $this->securityLog('parent_department_not_found');
                $this->response->setResponseStatus(404)
                        ->build();
                return;
            }
            if ($parent->getAccountInfo() !== $accountInfo) {
                $this->securityEvent('cross_account');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
            $result = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\DepartmentInfo')
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
                    ->getRepository('com\novaconcept\entity\DepartmentInfo')
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
            $this->response->setResponseStatus(409)
                    ->build();
            return;
        }

        if (isset($parent))
            $departmentInfo->setParent($parent);

        $this->bootstrap->getEntityManager()->persist($departmentInfo);
        $this->bootstrap->getEntityManager()->flush();

        $this->securityLog(201);
        $this->response->setResponseStatus(201)
                ->setResponseData($departmentInfo->getData())
                ->build();
    }

    public function edit() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
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
        $departmentInfo = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\DepartmentInfo', $this->request->getPathParamByName('id'));
        if ($departmentInfo == NULL) {
            $this->securityLog('department_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }

        if (($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                ($departmentInfo->getDescription() == "IS_CORPO" ||
                $departmentInfo->getDescription() == "IS_GROUP")) ||
                ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                $departmentInfo->getDescription() == "IS_CORPO")) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userAgencyPermission, $accountId) === TRUE &&
                ($departmentInfo != $this->userInfo->getDepartmentInfoCollection()->first())) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userGroupPermission, $accountId) === TRUE &&
                (($departmentInfo->getDescription() == "IS_GROUP" &&
                $departmentInfo != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()) ||
                ($departmentInfo->getDescription() == "IS_AGENCY" &&
                $departmentInfo->getParent() != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()))) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE &&
                (($departmentInfo->getDescription() == "IS_CORPO" &&
                $departmentInfo != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent()) ||
                ($departmentInfo->getDescription() == "IS_GROUP" &&
                $departmentInfo->getParent() != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent()) ||
                ($departmentInfo->getDescription() == "IS_AGENCY" &&
                $departmentInfo->getParent()->getParent() != $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent()))) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $name = (isset($this->request->getPostData()->name)) ? $this->request->getPostData()->name : $departmentInfo->getName();

        if (isset($this->request->getPostData()->parent)) {
            $parent = $this->bootstrap->getEntityManager()
                    ->find('com\novaconcept\entity\DepartmentInfo', $this->request->getPostData()->parent);
            if ($parent == NULL) {
                $this->securityLog('parent_department_not_found');
                $this->response->setResponseStatus(404)
                        ->build();
                return;
            }
            if ($parent->getAccountInfo() !== $accountInfo) {
                $this->securityEvent('cross_account');
                $this->response->setResponseStatus(403)
                        ->build();
                return;
            }
            $result = $this->bootstrap->getEntityManager()
                    ->getRepository('com\novaconcept\entity\DepartmentInfo')
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
                    ->getRepository('com\novaconcept\entity\DepartmentInfo')
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
            $this->securityLog('department_already_exists');
            $this->response->setResponseStatus(409)
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

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->build();
    }

    public function getByAccountInfoIdAgency() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired('is_agency_admin');

        $data = [];

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
                foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                    if ($agencyDepartment->getDescription() == "IS_AGENCY") {
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
                if ($agencyDepartment->getDescription() == "IS_AGENCY") {
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
            if ($agencyDepartment->getDescription() == "IS_AGENCY") {
                $agency = [];
                $agency['id'] = $agencyDepartment->getId();
                $agency['name'] = $agencyDepartment->getName();
                $agency['parentName'] = $agencyDepartment->getParent()->getName();
                array_push($data, $agency);
            }
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function corpo() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $data = [];

        $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
        $data['corpo'] = $corpoDepartment->getData();
        $data['groups'] = [];
        foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
            if ($groupDepartment->getDescription() == "IS_GROUP") {
                array_push($data['groups'], $groupDepartment->getData());
            }
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function group() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
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

        $data = [];

        $groupDepartment = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\DepartmentInfo', $this->request->getPathParamByName('id'));
        if ($groupDepartment == NULL) {
            $this->securityLog('group_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }
        if ($this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent() != $groupDepartment->getParent()) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }

        $data['group'] = $groupDepartment->getData();
        $data['agency'] = [];
        foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
            if ($agencyDepartment->getDescription() == "IS_AGENCY") {
                array_push($data['agency'], $agencyDepartment->getData(array('parent')));
            }
        }

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function agency() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
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

        $agencyDepartment = $this->bootstrap->getEntityManager()
                ->find('com\novaconcept\entity\DepartmentInfo', $this->request->getPathParamByName('id'));
        if ($agencyDepartment == NULL) {
            $this->securityLog('group_not_found');
            $this->response->setResponseStatus(404)
                    ->build();
            return;
        }
        if ($this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent() != $agencyDepartment->getParent()->getParent()) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
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

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function getAllUsers() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');
        $userGroupPermission = new Permission();
        $userGroupPermission->addRequired('is_group_admin');
        $userAgencyPermission = new Permission();
        $userAgencyPermission->addRequired('is_agency_admin');

        $data = [];

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === TRUE) {
            $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
            foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
                foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                    if ($agencyDepartment->getDescription() == "IS_AGENCY") {
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
                if ($agencyDepartment->getDescription() == "IS_AGENCY") {
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
            if ($agencyDepartment->getDescription() == "IS_AGENCY") {
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

        $this->securityLog(200);
        $this->response->setResponseStatus(200)
                ->setResponseData($data)
                ->build();
    }

    public function getAllAgencies() {
        $clientPermission = new Permission();
        $clientPermission->addRequired('can_manage_departments');
        $userCorpoPermission = new Permission();
        $userCorpoPermission->addRequired('is_corpo_admin');

        $accountId = $this->request->getPathParamByName('account_info_id');
        if ($this->userInfo->validatePermissions($userCorpoPermission, $accountId) === FALSE) {
            $this->securityLog('user_unauthorized');
            $this->response->setResponseStatus(403)
                    ->build();
            return;
        }
        $data = [];

        $corpoDepartment = $this->userInfo->getDepartmentInfoCollection()->first()->getParent()->getParent();
        foreach ($corpoDepartment->getChildrenCollection() as $groupDepartment) {
                foreach ($groupDepartment->getChildrenCollection() as $agencyDepartment) {
                    if ($agencyDepartment->getDescription() == "IS_AGENCY") {
                        array_push($data, $agencyDepartment->getData());
                    }
            }
        }
        $this->securityLog(200);
        $this->response->setResponseStatus(200)
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
