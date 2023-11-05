<?php
	include ('includes/connect.php');
	include ("includes/language.php");
	include ('includes/class.settlements.php');
	
	$settlements = new settlements();
	$settlements->setUser($_SESSION["osm_user"]);
        $settlements->setPDO($dbh);
	$stlm = $settlements->getOne($_GET["ids"]);
        
        $pagetitle = $stlm["name"];
        if ($pagetitle == "") { $pagetitle = "[no name]"; }
        $hasmap = "yes";
        $hasbing = "yes";
        //zoom 15 nice for hamlet and village, 15 for town og 14 for city?
        $zoom = 16;
        if ($stlm["place"] == "city") { $zoom = 14; }
        if ($stlm["place"] == "town") { $zoom = 15; }
        if ($stlm["place"] == "village ") { $zoom = 15; }
        
	include ('includes/header.php');

        $settlements->trailMaker($stlm["id_country"], $stlm["region"], $stlm["subregion"]);
        $closest = $settlements->closestSettlements($_GET["ids"]);
        if ($stlm["id_country"] == 216)
        {
            $closestName = $settlements->closestSettlementsLesotho($_GET["ids"]);
        }       
?>
<h2><?= $pagetitle; ?></h2>
<div class="setutitle"><label class="placetype"><?= $stlm["place"]; ?></label> <?= $stlm["name_en"]; ?> <?= $settlements->osmLink($stlm["lat"], $stlm["lon"]); ?> - 
    <?= "<a href='http://www.openstreetmap.org/edit#map=17/" . $stlm["lat"] . "/" . $stlm["lon"] . "' target='_blank' title='" . $stlm["lat"] . " " . $stlm["lon"] . "'>"._editOSM."</a>"; ?>
 <?php if ($_SESSION["osm_user"] != "") { echo "(".$settlements->josmLink($stlm["lat"], $stlm["lon"]).")"; } ?>
<br />
<table class="setlist" id="jardir" width="180" style="position:absolute; top: 150px; left:450px; z-index:31;";>
    <tr class='edgerow'>
        <th colspan=4>Remote mapping</th>
    </tr>
    <tr>
        <th class="mini"><?= _network; ?></th>
        <th class="mini"><?= _streets; ?></th>
        <th class="mini"><?= _buildings; ?></th>
        <th class="mini"><?= _imagery; ?></th>
    </tr>
    <tr>
    <?php 
    $zindex = 30;
    echo $settlements->makeField($zindex, "network", $stlm["id_area"], $stlm["name"], $stlm["network"]);
    $zindex--;
    echo $settlements->makeField($zindex, "streets", $stlm["id_area"], $stlm["name"], $stlm["streets"]);
    $zindex--;
    echo $settlements->makeField($zindex, "buildings", $stlm["id_area"], $stlm["name"], $stlm["buildings"]);
    $zindex--;
    echo $settlements->makeField($zindex, "imagery", $stlm["id_area"], $stlm["name"], $stlm["imagery"]);
    $zindex--;
    ?>
    </tr>
    <tr class='edgerow'>
        <th colspan=4>Local mapping</th>
    </tr>
    <tr>
        <th class="mini"><?= _addresses; ?></th>        
        <th class="mini"><?= _amenities; ?></th>
        <th class="mini"><?= _paths; ?></th>
        <th class="mini"><?= _mapillary; ?></th>
    </tr>    
    <tr>
    <?php    
    echo $settlements->makeField($zindex, "addresses", $stlm["id_area"], $stlm["name"], $stlm["addresses"]);
    $zindex--;
    echo $settlements->makeField($zindex, "amenities", $stlm["id_area"], $stlm["name"], $stlm["amenities"]);
    $zindex--;
    echo $settlements->makeField($zindex, "paths", $stlm["id_area"], $stlm["name"], $stlm["paths"]);
    $zindex--;
    echo $settlements->makeField($zindex, "mapillary", $stlm["id_area"], $stlm["name"], $stlm["mapillary"]);
    $zindex--;
    ?>
</tr>
</table>
<?php
$zindex = 30;
?>
    

<div style="min-height: 80px">
    <ul>
    <b>Closest settlements</b><br/>
<?php
    foreach($closest as $close)
    {
        $closename = $close["name"];
        if ($closename == "") { $closename = "[no name]"; }
        $returner = '<li><a href="settlement.php?ids='.$close["id"].'"><label class="placename" title="'.$close["name_en"].'">'.$closename.'</label></a>';
        $returner .= ' (distance: '.number_format(round($close["distance"], 2), 2).' km)</li>';
        echo $returner;
    }
    
    if ($closestName != "")
    {
        echo "<b>Lesotho names</b>";
        
        foreach ($closestName as $close)
        {
            $closename = $close["village"];
            if ($closename == "") { $closename = "[no name]"; }
            $returner = '<li><label class="placename" title="'.$close["village"].'">'.$closename.'</label></a>';
            $returner .= ' (distance: '.number_format(round($close["distance"], 2), 2).' km)</li>';
            echo $returner;
        }
    }
    
    if ($returner == "")
    {
        echo "<br/><br/><br/>";
    }
?>
    </ul>
</div>
<!-- CSS -->
    <style type="text/css">
    
        .w3c { min-height: 400px; position: relative; width: 600px; }
        .w3c > div { display: inline; z-index: 5; }
        .w3c > div > a { margin-left: -1px; position: relative; left: 1px; text-decoration: none; color: black; background: white; display: block; float: left; padding: 5px 10px; border: 1px solid #ccc; border-bottom: 1px solid white; }
        .w3c > div:not(:target) > a { border-bottom: 0; background: -moz-linear-gradient(top, white, #eee); }	
        .w3c > div:target > a { background: white; font-weight: bold; }	
        .w3c > div > div { background: white; z-index: 2; left: 0; top: 30px; bottom: 0; right: 0; padding: 0px; border: 1px solid #ccc; }	
        .w3c > div:not(:target) > div { position: absolute; z-index: 3; }
        .w3c > div:target > div { position: absolute; z-index: 4; }

    </style>
 <div class="w3c">
 
<!-- Bing -->
<div id="Bing"><a href="#Bing">Bing</a>
      <div id='mapDiv' style="width:600px; height:400px;"></div>    
</div>

<!-- MAPBOX -->
<div id="Mapbox"><a href="#Mapbox">Mapbox</a>
<div><iframe id="mapboxmap" width='600px' height='400px' frameBorder='0' src='https://a.tiles.mapbox.com/v4/<?= MAPBOX_MAP ?>/attribution,zoompan,zoomwheel,geocoder,share.html?access_token=<?= MAPBOX_TOKEN ?><?= "#".$zoom."/".$stlm["lat"]."/".$stlm["lon"]; ?>'></iframe></div>
</div>

<!-- Mapillary -->
<div id="Mapillary"><a href="#Mapillary">Mapillary</a>
<div id="mapil" style="width: 600px; height: 400px"></div>
</div>
<script>

 var map = new L.map('mapil').setView([<?= $stlm["lat"].",".$stlm["lon"];?>], <?= $zoom; ?>);
 
  L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '? OpenStreetMap contributors'
  }).addTo(map);

  var config = {
    url: "https://d2munx5tg0hw47.cloudfront.net/tiles/{z}/{x}/{y}.mapbox"
  };

  var mapillarySource = new L.TileLayer.MVTSource(config);
  map.addLayer(mapillarySource);
  
</script>

<!-- OSM -->
 <div id="OSM"><a href="#OSM">OSM</a>
    <div id="map" style="width: 600px; height: 400px; clear:both; border: 1px solid black;"></div>
 </div>
<script>
var map = L.map('map').setView([<?= $stlm["lat"]; ?>, <?= $stlm["lon"]; ?>], <?= $zoom; ?>);
		
	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors, map data is <a href="https://opendatacommons.org/licenses/odbl/">ODbL</a>, map tiles are <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
    maxZoom: 18
	}).addTo(map);
</script>
 </div>
<?php 
include("includes/javascripts.php");
include("includes/footer.php");
