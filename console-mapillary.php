<?php
include ("includes/console-connect.php");
        
include ("includes/importer.php");
ERROR_REPORTING(E_ERROR);
$automater = new automater();
$automater->setPDO($dbh);

//To seed a single country
//Ísland 122  
//Uganda 144
$country = 216;
$region = "";
$subregion = "";
$nullonly = "no";

//Check Mapillary
system("chcp 65001");
//$automater->mapillaryByRegion($country, $nullonly);
/*
while(true)
{
    if ($automater->mapillaryCountryCheck("no") == "nothing")
    {
        //wait 30 minutes
        echo date('H:i:s', time())." \n";
        sleep(1800);
    }
}
*/
$lon = "-21.9048106";
$lat = "64.1113504";
$dude =  $automater->mapillaryLookupClose($lat, $lon);
echo $dude;

echo "\n--fini!!--";