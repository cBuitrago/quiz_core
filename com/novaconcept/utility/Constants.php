<?php

namespace com\novaconcept\utility;

class Constants {

    /**
     * PERMISSIONS
     */
    const GOD = 'is_god';
    const CORPO = 'is_corpo_admin';
    const GROUP = 'is_group_admin';
    const AGENCY = 'is_agency_admin';
    const USER = 'is_user';
    
    /**
     * GET PARAMETERS 
     */
    const ID = 'id';
    const ACCOUNT = 'account_info_id';
    const DATE = 'Date';
    const AUTHORIZATION = 'Authorization';
    
    /**
     * DESCRIPTION DEPARTMENT
     */
    const DESC_CORPO = 'IS_CORPO';
    const DESC_GROUP = 'IS_GROUP';
    const DESC_AGENCY = 'IS_AGENCY';
    
    /**
     * HTTP STATUS CODES
     */
    const OK = 200;
    const CREATED = 201;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const CONFLICT = 409;
    const INTERNAL_SERVER_ERROR = 500;
    
    /**
     * SECURITY EVENTS
     */
    const UNAUTHENTICATED_STR = 'Unauthenticated';
    const UNAUTHORIZED_STR = 'Unauthorized';
    const NOT_FOUND_STR = 'Not_Found';
    const CONFLICT_STR = 'Already_Exists';
    
    /**
     * REPOSITORIES
     */

}
