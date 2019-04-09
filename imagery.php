<?php
	include ('includes/connect.php');
	include ("includes/language.php");
	include ('includes/class.settlements.php');
	
	$settlements = new settlements();
	$settlements->setUser($_SESSION["osm_user"]);
        $settlements->setPDO($dbh);
        $settlements->updateCountriesMissingImagery();
	$stlm = $settlements->imageryReview($_GET["idc"], $_GET["region"], $_GET["sub"]);
        $dropdownData = $settlements->countriesMissingImagery();
        
        $now = time() - date('Z')+1;
        $today = date("Y-m-d H:00:00", $now);
        $lastgen = strtotime($dropdownData[0]["last_generated"]);
        $nowtime = strtotime($today)-2*60*60; //subtract 2 hours
        
        if ($lastgen < $nowtime)
        {
            //more than 2 hours since refresh - update imagery
            $settlements->updateCountriesMissingImagery();
            $dropdownData = $settlements->countriesMissingImagery();
        }
        
        if (!$stlm["Error"] == "yes")
        {
            $pagetitle = $stlm["name"];
            if ($pagetitle == "") { $pagetitle = "[no name]"; }
            $hasmap = "yes";
            $hasbing = "yes";
        }
        else
        {
            $pagetitle = "no more";
            $settlements->updateCountriesMissingImagery();
            $dropdownData = $settlements->countriesMissingImagery();
        }
        //zoom 16 er fínt fyrir hamlet og village, 15 fyrir town og 14 fyrir city?
        $zoom = 16;
        /*
        if ($stlm["place"] == "city") { $zoom = 14; }
        if ($stlm["place"] == "town") { $zoom = 15; }
        if ($stlm["place"] == "village ") { $zoom = 15; }
        */
	include ('includes/header.php');

        $settlements->trailMaker($stlm["id_country"], $stlm["region"], $stlm["subregion"]);
//        $closest = $settlements->closestSettlements($_GET["ids"]);
        //$activity = $settlements->getActivity($_GET["ids"]);


?>
<!-- country picker -->
<div style="float:right">
    <form>Country picker:<br/>
        <select name="idc" id="idcMissing" onChange="replaceFilter('idcMissing', 'idc')">
            <option value="">-random-</option>
            <?php
            foreach ($dropdownData as $drop)
            {
                echo "<option value=".$drop["id"];
                if ($_GET["idc"] == $drop["id"]) { echo " selected"; }
                echo ">".$drop["name_en"]."</option>";
            }
            ?>
        </select>
    </form>
</div>
<?php
if (!$stlm["Error"] == "yes")
{
?>

<h2>Imagery - <a href="settlement.php?ids=<?= $stlm["id_settlement"] ?>"><?= $pagetitle; ?></a></h2>
 <?php //if ($_SESSION["osm_user"] == "") { echo "You need to be logged in to do this"; exit; } ?>
<?php //if ($stlm["Error"]=="yes") { echo "No more imagery left to evalute for this country"; exit; ?>
<br />

<?php
$zindex = 30;
?>
    
<!-- Bing -->
<table border="0" style="position:relative;display:block">
    <tr>
        <td colspan="2" align="center">
            <table border="1">
                    <tr><th colspan="3">Only one of the windows below needs to show usable imagery, not both.<br/>
                            You can use keyboard shortcuts instead of clicking.</th></tr>
                <tr>
                    <td class="good" width="33%"><label id="goodImagery" onclick="imageryClick(<?= $stlm["id_area"]; ?>, 100)">[1] <?= $settlements->textField("imagery", "100"); ?></label></td>
                    <td class="partial" width="34%"><label id="partialImagery" onclick="imageryClick(<?= $stlm["id_area"]; ?>, 50)">[2] <?= $settlements->textField("imagery", "50"); ?></label></td>
                    <td class="bad" width="33%"><label id="badImagery" onclick="imageryClick(<?= $stlm["id_area"]; ?>, 0)">[3] <?= $settlements->textField("imagery", "0"); ?></label></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr><td> 
            <div><iframe width="500" height="400" frameborder="0" src="http://www.bing.com/maps/embed/viewer.aspx?v=3&cp=<?= $stlm["lat"];?>~<?= $stlm["lon"];?>&lvl=<?= $zoom; ?>&w=500&h=400&sty=a&typ=d&pp=&ps=&dir=0&mkt=en-us&form=BMEMJS"></iframe></div>
        </td>
<!-- MAPBOX -->
<td><iframe id="mapboxmap" width='500px' height='400px' frameBorder='0' src='https://a.tiles.mapbox.com/v4/stalfur.m96g6f9a/attribution,zoompan,zoomwheel,geocoder,share.html?access_token=pk.eyJ1Ijoic3RhbGZ1ciIsImEiOiJYT2xBaFN3In0.v_vvJeC2QQgopWUR7-oPkQ<?= "#".$zoom."/".$stlm["lat"]."/".$stlm["lon"]; ?>'></iframe></td>
    </tr>
</table>
<script>
    $(document).keypress(function(e) {
    switch(e.which) {
        case 49: // 1
            imageryClick(<?= $stlm["id_area"]; ?>, 100);
        break;
        
        case 50: // 2
            imageryClick(<?= $stlm["id_area"]; ?>, 50);
        break;
        
        case 51: // 3
            imageryClick(<?= $stlm["id_area"]; ?>, 0);
        break;
        
        case 52: // 4
            location.reload();
        break;

        default: return; // exit this handler for other keys
    }
});
</script>
    <?php 
}
else
{   
    $text = "No more imagery to evaluate! Pick another country if you want to continue.";
    if ($_GET["region"] != "")
    {
        $text = "No more imagery to evalute for this region or subregion.";
    }
    
    ?>
<b><?= $text; ?></b>
<?php
} 

include("includes/javascripts.php");
include("includes/footer.php");?>