<?php
include ("includes/console-connect.php");
include ("includes/importer.php");


$importer = new importer();
$importer->setPDO($dbh);
$importer->setUser(OSM_ROBOT_USER);

$automater = new automater();
$automater->setUser(OSM_ROBOT_USER);
$automater->setPDO($dbh);

//To seed a single country, examples
//Iceland 122
//Botswana 105
//Lesotho 216
//Turkey 313
//Tanzania 120

$country = $argv[1];
$missing= "yes";

system("chcp 65001");
$countries = $importer->getUnmappedCountries();

foreach($countries as $current)
{
    $country = $current["id"];

    echo $importer->getCountry($country)."\n\n";
    echo "settlements\n";
    $importer->settlements($country, "city");
    $importer->settlements($country, "town");
    $importer->settlements($country, "village");
    $importer->settlements($country, "hamlet");
    //echo "nominatim\n";
    //$importer->loadCountryNominatim($country, "yes");
    //POPULATE regions and areas
    //$importer->nominatimByRules($country);
    echo "populate\n";
    $importer->populateAreas($country);
    //echo "overpass\n";
    //$automater->overpassByCountry($country, "", "", $missing);
    //$automater->updateFromOverpassByRegion($country);
    //echo "mapillary\n";
    //$automater->mapillaryByRegion($country, $nullonly);
    echo "fini!!";
} 