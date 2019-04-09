<?php
	include ('../includes/connect.php');
	include ('../includes/class.osmuser.php');
	$osmUser = new osmUser();
	
	$use_josm = strip_tags(mysql_real_escape_string($_REQUEST['use_josm']))*1;
	
	$osm_id = $_SESSION["osm_id"];
	$_SESSION["use_josm"] = $use_josm;
	
	$osmUser->updatePreferences($sql, $osm_id, $use_josm);

	return true;
?>
