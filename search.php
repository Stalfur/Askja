<?php
	include ('includes/connect.php');
	include ("includes/language.php");
	include ('includes/class.settlements.php');
	
	$settlements = new settlements();
	$settlements->setUser($_SESSION["osm_user"]);
        $settlements->setPDO($dbh);
	
        $pagetitle = "Search for ".$_GET["search"];
        
	include ('includes/header.php');
?>
<h2><?= $pagetitle; ?></h2>

<?php
$stlm = $settlements->searchSettlement($_GET["search"]);
if ($stlm["Error"] == "yes")
{
    echo "Not found";
}
else
{
    echo "<ul>";
    foreach($stlm as $set)
    {
        $thisrow = "";
        $thisrow .= '<li><label class="placetype">'.$set["place"].'</label> ';
        $thisrow .= '<label class="placename" title="'.$set["name_en"].'"><a href="settlement.php?ids='.$set["id_settlement"].'">'.$set["name"].'</a></label> (';
        if ($set["subregion"] != "") { $thisrow .= $set["subregion"].", "; }
        if ($set["region"] != "") { $thisrow .= $set["region"].", "; }
        $thisrow .= $set["countryname_en"].") <label class='placetype'>".$set["lat"]." ".$set["lon"]."</label></li>";
        echo $thisrow;
    }
    echo "</ul>";
}
?>

<?php 
include("includes/javascripts.php");
include("includes/footer.php");?>