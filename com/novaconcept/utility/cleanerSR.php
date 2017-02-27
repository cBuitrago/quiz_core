<?php

define("HOST", "localhost");
define("USER", "root");
define("PASS", "root");
date_default_timezone_set ("UTC");
$dsn = "mysql:host=".HOST.";dbname=core";
$user = USER;
$pass = PASS;
$id = new PDO($dsn, $user, $pass);
$date = time() - 3600;
$dDate = new DateTime();
$requete = "DELETE FROM security_replay WHERE created_on < '".$dDate->setTimestamp($date)->format('Y-m-d H:i:s')."'";
$nb = $id->exec($requete);

