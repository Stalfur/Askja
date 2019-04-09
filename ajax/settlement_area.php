<?php
	ini_set('include_path', '../');
        include("includes/connect.php");
	include ('includes/class.settlements.php');
	
	$settlements = new settlements();
        $settlements->setPDO($dbh);
	$settlements->setUser($_SESSION["osm_user"]);
	
	$id = strip_tags(mysql_real_escape_string($_REQUEST["id"]));
	$field = strip_tags(mysql_real_escape_string($_REQUEST["field"]));
	$value = strip_tags(mysql_real_escape_string($_REQUEST["value"]));
	
	$returner = $settlements->updateField($id, $field, $value);
        header('Content-type: application/json');
        echo $returner;