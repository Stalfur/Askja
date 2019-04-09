<?php
include("includes/connect.php");

$wherefrom = $_SERVER['HTTP_REFERER'];
list($a, $b) = explode("\?", $wherefrom);

$newlang = htmlspecialchars($_GET["ling"]);

setcookie ("lang", $newlang, time()+3600000);
setcookie ("lang", $newlang, time()+3600000,"/",COOKIE_DOMAIN);

header("Location: ".getenv("HTTP_REFERER"));
?>