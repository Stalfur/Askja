<?php
$lang = $_COOKIE["lang"];


//ef við fáum tungumál sent inn annars staðar frá:

if ($_REQUEST["ling"] != "") {
	$ling = strtoupper($_REQUEST["ling"]);
//	setcookie("lang", $_REQUEST["ling"],time()+360000);
	$ling = strtolower($_REQUEST["ling"]);
	include("includes/language_$ling.php");
}
else
{
	//ef ekkert tungumál valið eða til
	switch($lang)
	{
		case "ENG":
			include "./includes/language_eng.php";
			break;
		case "ISL":
			include "./includes/language_isl.php";
			break;
		default:
			include "./includes/language_eng.php";
			$lang = "ENG";
			break;
	}
}
include "./includes/icons.php";
?>
