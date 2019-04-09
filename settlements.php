<?php
include ('includes/connect.php');
include ("includes/language.php");
include ('includes/class.settlements.php');

$settlements = new settlements();
$settlements->setUser($_SESSION["osm_user"]);
$settlements->setEditor($_SESSION["use_josm"]);
$settlements->setPDO($dbh);

$pagetitle = $settlements->pageTitle($_GET["idc"], $_GET["region"], $_GET["sub"]);
$imagerylink = "imagery.php?idc=".$_GET["idc"]."&region=".$_GET["region"]."&sub=".$_GET["sub"];
$imagerytext = "evaluate imagery for ".$_GET["sub"].", ".$_GET["region"];
include ('includes/header.php');
include("includes/javascripts.php");

$settlements->trailMaker($_GET["idc"], $_GET["region"], $_GET["sub"]);
?>
<div style="float:right;margin:5px; padding:10px;border:5px solid #990000;max-width:150px"><a href="<?= $imagerylink;?>"><?= $imagerytext;?></a></div>
<?php
if ($_GET["idc"] == "")
{
    $settlements->countryOverview();
}
elseif ($_GET["idc"] != "" && $_GET["region"] == "")
{
    $settlements->regionOverview($_GET["idc"]);
}
elseif ($_GET["region"] != "")
{
    echo $settlements->getSettlements($_GET["idc"], $_GET["region"], $_GET["sub"], $_GET["p"], $_GET["n"], $_GET["s"], $_GET["b"], $_GET["i"], $_GET["a"], $_GET["e"], $_GET["t"], $_GET["m"]);
}


?>
<?php 

include("includes/footer.php");?>