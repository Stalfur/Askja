<?php
include ("includes/console-connect.php");
        
include ("includes/importer.php");
ERROR_REPORTING(E_ERROR);

$automater = new automater();
$automater->setUser(OSM_ROBOT_USER);
$automater->setPDO($dbh);

$missing= "yes";
system("chcp 65001");
$country = 216; //put in ID of country

$automater->updateFromOverpassByRegion($country, "yes");

echo "\n--fini!!--";