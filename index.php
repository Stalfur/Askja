<?php
    include ('includes/connect.php');
    include("includes/language.php");
    include ("includes/class.osmuser.php");
    $osmUser = new osmUser();
    $osmUser->setPDO($dbh);
    $pagetitle = "Askja - An OSM Quality Overview";
    include ('includes/header.php');
?>
<div style="float:right">
<?php
/*
        $actions = $osmUser->getUserActivity();
        if ($actions["Error"] != "yes")
        {
        ?>
        <h2>Latest updates</h2>
        <?php
        foreach ($actions as $entry)
        {
            if ($entry["item_table"] == "settlement_areas")
            {
                if ($entry["name"] == "") { $entry["name"] = "[no name]"; }
                $printout = $entry["osm_user"]." updated <label class='placetype'>".$entry["place"]."</label>"
                        ." <a href='settlement.php?ids=".$entry["id_settlement"]."'><label class='placename' title=\"".$entry["name_en"]."\">".$entry["name"]."</label></a>"
                        ." in ";
                if ($entry["subregion"] != "")
                {
                    $printout .= $entry["subregion"].", ";
                }
                $printout .= $entry["region"].", <label title=\"".$entry["countryname_en"]."\">".$entry["countryname"]."</label><br />";
                echo $printout;
            }
            else
            {
                $printout = $entry["osm_user"]." updated <label class='placetype'>".$entry["place"]."</label>"
                        ." <a href='farm.php?idf=".$entry["item_id"]."'><label class='placename' title=\"".$entry["name_en"]."\">".$entry["name"]."</label></a>"
                        ." in ";
                if ($entry["subregion"] != "")
                {
                    $printout .= $entry["subregion"].", ";
                }
                $printout .= $entry["region"].", <label title=\"".$entry["countryname_en"]."\">".$entry["countryname"]."</label><br />";
                echo $printout;
            }
        }
        }
 * 
 */
?>
</div>
<div style="float:right;margin:5px; padding:10px;border:5px solid #990000;">Have 5 minutes?<br />Help out and <a href="imagery.php">evaluate imagery</a></div>
<div style="width:400px;padding-left:10px;">
<h1>Welcome to Askja</h1>
<p>This is a data quality tool for <a href="http://www.openstreetmap.org/">OpenStreetMap</a>. This website is run by <a href="http://www.hlidskjalf.is/english/">OpenStreetMap á Íslandi</a>. </p>
<p>You can login as an OSM-user and then update values on this website.</p>

<h3>Settlements</h3>
<p>Using Overpass and Nominatim to extract all place-nodes that are city/town/village/hamlet we can now categorize them according to their current data quality. A human eye and hand is required for some of the evaluation but some parts of it are automated, at least to move status from Unknown to Partial. This tool also assists in identifying settlements missing names.</p>
<p>Currently you can update the status of <a href="settlements.php">settlements</a> in several countries (more to be added) as well as update
    data for <a href="logbyli.php">farms and estates in Iceland</a>.</p>

<p>Clicking on the black bar at the top will close each popup without modifying values.</p>
<p>Future updates include a tasking manager, allowing someone to pick a task that is easily doable, for example connect a settlement to the road network, add streets within a settlement or add buildings where imagery quality has been deemed good.</p>
<p>You can <a href="https://github.com/Stalfur/Askja/issues">post feedback on Github</a>.</p>
</div>
<?php include("includes/footer.php");