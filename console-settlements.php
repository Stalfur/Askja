<?php
include ("includes/console-connect.php");
include ("includes/importer.php");


$importer = new importer();
$importer->setPDO($dbh);

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
//Tanzania 120
$country = 216;
echo $importer->getCountry($country)."\n\n";
//$importer->nominatimByRules($country);
$importer->settlements($country, "city");
$importer->settlements($country, "town");
$importer->settlements($country, "village");
$importer->settlements($country, "hamlet");
//$importer->populateAreas($country);


echo "fini!!";