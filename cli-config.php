<?php

require_once __DIR__.'/vendor/autoload.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use com\novaconcept\utility\ApiConfig;
use com\novaconcept\utility\Bootstrap;

ApiConfig::setFileLocation("api_config.json");
$bootstrap = new Bootstrap();

$entityManager = $bootstrap->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
