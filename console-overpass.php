<?php
include ("includes/console-connect.php");
        
include ("includes/importer.php");
ERROR_REPORTING(E_ERROR);
$importer = new importer();
$importer->setUser("Riddari");
$importer->setSQL($sql);

$automater = new automater();
$automater->setUser("Riddari");
$automater->setSQL(clone $sql);

//To seed a single country
//Ísland 122  
//Botswana 105 x 
//Lesotho 216 x
//Zimbabwe 198 
//Færeyjar 95 
//Namibia 80
//Suður Afríka 57
//Andorra 6 x
//Qatar 164 
//Turkey 313
//Iran 60 x
//Ireland 191 
$country = 216;
$missing= "no";

system("chcp 65001");

//Check Overpass
//$automater->overpassByCountry($country, "", "", $missing);
//$automater->overpassByRegion($country, $missing);
//$importer->populateAreas($country);
$automater->updateFromOverpassByRegion($country, $missing);
//$automater->overpassSettlement(194834);

/*
while(true)
{
    if ($automater->overpassCountryCheck() == "nothing")
    {
        //wait 30 minutes
        echo date('H:i:s', time())." \n";
        sleep(300);
    }
}
*/
echo "\n--fini!!--";