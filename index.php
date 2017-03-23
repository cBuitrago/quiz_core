<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * server requirements
 * apache >= 2.2
 * php >= 5.4
 * mysql >= 
 */
require_once __DIR__.'/vendor/autoload.php';

use com\novaconcept\utility\ApiConfig;
use com\novaconcept\utility\RoutUtil;

date_default_timezone_set ("UTC");

ApiConfig::setFileLocation("api_config.json");

if (ApiConfig::getData()->settings->mode == "development")
{
    error_reporting(-1);
    ini_set('display_errors', 'On');
}
$routUtil = new RoutUtil();
$routUtil->excecute();