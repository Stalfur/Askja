<?php
    include ('includes/connect.php');
    include ("includes/language.php");
    include ("includes/class.osmuser.php");
    $osmUser = new osmUser();
    $osmUser->setPDO($dbh);
    $thisuser = $osmUser->isUser($_GET["user"]);
    $pagetitle = $thisuser["osm_user"];
    include ('includes/header.php');

    if ($thisuser["Error"] == "yes")
    {
        echo "<h2>User not found</h2>";
        include("includes/footer.php");
        exit;
    }
    else
    {
    ?>

<h1><?= $thisuser["osm_user"];?></h1>

        <?php
        $actions = $osmUser->getUserActivity($_GET["user"]);
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
                $printout = "Updated <label class='placetype'>".$entry["place"]."</label>"
                        ." <a href='settlement.php?ids=".$entry["item_id"]."'><label class='placename' title=\"".$entry["name_en"]."\">".$entry["name"]."</label></a>"
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
                $printout = "Updated <label class='placetype'>".$entry["place"]."</label>"
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
?>
</table>

<?php }
    }
include("includes/footer.php");