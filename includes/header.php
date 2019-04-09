<?php
	if ($_SERVER["REQUEST_URI"] != '/login.php') 
	{ 
		$_SESSION["lastvisited"] = $_SERVER["REQUEST_URI"];
	}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?= $pagetitle; ?></title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="robots" content="index, follow" />
	<link type="text/css" rel="stylesheet" href="style.css" />
	<script src='osmauth.js'></script>
	<script src='jquery-2.1.0.min.js'></script>
	<script src='store.js'></script>
        <?php if ($hasmap == "yes") { ?>
	<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.css" />
	<script src="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.js"></script>
        <script src="Leaflet.MapboxVectorTile.js"></script>
        <script language="JavaScript" src="leaflet-hash.js"></script>
        <?php } ?>
        <?php if ($hasbing == "yes") { 
            $body .= 'onLoad="GetMap()"';?>
        <script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0&mkt=en-gb"></script>
        <script type="text/javascript">
            function GetMap()
            {   

               var map = new Microsoft.Maps.Map(document.getElementById("mapDiv"), {credentials:"<?= MICROSOFT_TOKEN ?>", center: new Microsoft.Maps.Location(<?= $stlm["lat"].",".$stlm["lon"];?>), zoom: <?= $zoom; ?>, mapTypeId:Microsoft.Maps.MapTypeId.aerial, labelOverlay: Microsoft.Maps.LabelOverlay.hidden });
            }
        </script>
        <?php } else { $body = ""; } ?>
</head>
    <body <?= $body; ?> >
<div id="header">
            <div id="searcher"><form name="searcher" method="get" action="search.php">
                    Find settlement <input type="text" width="20" name="search" /><input type="submit" value="search" />
            </form></div>
	<div id="headtitle">Askja</div>
	An OpenStreetMap Quality Overview Tool
</div>
	<div id="navigation1">
		<a href="/index.php"><?= _home;?></a> | 
                <a href="/settlements.php"><?= _settlements;?></a> | 
		<a href="/about.php"><?= _about;?></a> |
		<span id='display_name'>
		<?php
	if ($_SESSION['osm_user'] != "")
		echo $_SESSION['osm_user'].'</span> (<a href="preferences.php">'._preferences.'</a>)&nbsp; &nbsp;<a href="login.php">'._logout.'</a>';
	else {
		echo '</span> <a href="login.php">'._login.'</a>';
	}
	?>
		</span>
	
	</div>