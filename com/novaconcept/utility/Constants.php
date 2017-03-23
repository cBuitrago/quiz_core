<?php

namespace com\novaconcept\utility;

class Constants {

    /**
     * PERMISSIONS
     */
    const AGENCY = 'is_agency_admin';
    const CORPO = 'is_corpo_admin';
    const GOD = 'is_god';
    const GROUP = 'is_group_admin';
    const USER = 'is_user';
    
    /**
     * GET PARAMETERS 
     */
    const ACCOUNT = 'account_info_id';
    const AUTHORIZATION = 'Authorization';
    const DATE = 'Date';
    const ID = 'id';
    
    /**
     * DESCRIPTION DEPARTMENT
     */
    const DESC_AGENCY = 'IS_AGENCY';
    const DESC_CORPO = 'IS_CORPO';
    const DESC_GROUP = 'IS_GROUP';
    
    /**
     * HTTP STATUS CODES
     */
    const BAD_REQUEST = 400;
    const CONFLICT = 409;
    const CREATED = 201;
    const FORBIDDEN = 403;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_FOUND = 404;
    const OK = 200;
    const UNAUTHORIZED = 401;
    
    /**
     * PERMISSION_ID
     */
    const AGENCY_PERMISSION_ID = 16;
    const CORPO_PERMISSION_ID = 14;
    const GROUP_PERMISSION_ID = 15;
    const USER_PERMISSION_ID = 17;
    
    /**
     * SECURITY EVENTS
     */
    const CALL_EXPIRED = 'call_expired';
    const CLIENT_MISSING = 'client_missing';
    const CLIENT_NOT_AUTHORIZED = 'client_not_authorized';
    const CONFLICT_STR = 'already_exists';
    const CROSS_ACCOUNT = 'cross_account';
    const NOT_FOUND_STR = 'not_found';
    const REPLAY = 'replay';
    const REQUEST_EXPIRED = 'request_expired';
    const UNAUTHENTICATED_STR = 'unauthenticated';
    const UNAUTHORIZED_STR = 'unauthorized';
    const USER_MISSING = 'user_missing';
    const USER_NOT_AUTHORIZED = 'user_not_authorized';
    const USER_NOT_FOUND = 'user_not_found';
    
    /**
     * REPOSITORIES
     */
    const ACCOUNT_INFO_REP = 'com\novaconcept\entity\AccountInfo';
    const CLIENT_ACCOUNT_REP = 'com\novaconcept\entity\ClientAccount';
    const CLIENT_AUTHENTICATION_REP = 'com\novaconcept\entity\ClientAuthentication';
    const DEPARTMENT_AUTHORIZATION_REP = 'com\novaconcept\entity\DepartmentAuthorization';
    const DEPARTMENT_INFO_REP = 'com\novaconcept\entity\DepartmentInfo';
    const PROGRESS_INFO_REP = 'com\novaconcept\entity\ProgressInfo';
    const QUIZ_GROUP_QUIZ_LIST_REP = 'com\novaconcept\entity\QuizGroupQuizList';
    const QUIZ_REP = 'com\novaconcept\entity\Quiz';
    const QUIZ_RESULTS_REP = 'com\novaconcept\entity\QuizResults';
    const SECURITY_REPLAY_REP = 'com\novaconcept\entity\SecurityReplay';
    const USER_ACCOUNT_REP = 'com\novaconcept\entity\UserAccount';
    const USER_AUTHENTICATION_REP = 'com\novaconcept\entity\UserAuthentication';
    const USER_AUTHORIZATION_REP = 'com\novaconcept\entity\UserAuthorization';
    const USER_INFO_REP = 'com\novaconcept\entity\UserInfo';
    const USER_PERMISSION_REP = 'com\novaconcept\entity\UserPermission';
}
