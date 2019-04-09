<?php
include ("includes/console-connect.php");
include ("includes/importer.php");
error_reporting(1);

$importer = new importer();
//$importer->setUser($_SESSION["osm_user"]);
$importer->setPDO($dbh);

//To seed continents
//$importer->continents();

//To seed countries
//$importer->countries();

//To seed a single country
//Ísland 122
//Botswana 105
//Lesotho 216
//Zimbabwe 198
//Færeyjar 95
//Namibia 80
//Suður Afrík 57
//Andorra 6
//Qatar 164 ------
//Turkey 313
$country = 216;
//$country = $argv[1];
echo $importer->getCountry($country)."\n\n";
//NOMINATIM LOOKUP 
//MapQuest Nominatim
//$importer->setUrlNom("http://open.mapquestapi.com/nominatim/v1/reverse.php?format=xml&addressdetails=1&accept-language=xx");
/*
$importer->loadCountryNominatim($country, "yes");

//POPULATE regions and areas
$importer->nominatimByRules($country);
*/
while(true)
{
	$importer->resetNominatim();
    if ($importer->nominatimCountryCheck() == "nothing")
    {
        //wait 5 minutes
        $importer->resetNominatim();
        echo date('H:i:s', time())." \n";
        sleep(300);
    }
}

echo "fini!!";
?>