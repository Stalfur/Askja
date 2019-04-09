<?php
class settlements extends common
{
    private $osmUser;
    private $pdo;
    private $osmEditor;
    
    public function setUser($name) {
        $this->osmUser = $name;
    }
    
    //1 = JOSM, 0=iD
    public function setEditor($editor) {
        $this->osmEditor = $editor;
    }

    public function setPDO($tsql) {
        $this->pdo = $tsql;
    }
/*
 * Fields used - basic and extended
 * -unknown
 * -bad
 * -partial
 * -good
 * basic:
    * network - connected to road network (or ferry, airport?)
    * streets - internal road system
    * buildings - drawn by imagery or imports
    * imagery - imagery quality (mostly Bing/Mapbox)
 * extended:
    * addresses - requires street names and house numbers, or other system
    * amenities - schools, police, healthcare mostly
    * paths - walking, cycling
    * mapillary - streetview style images
 * shops and other POI will be covered in a different way with counters (amenities included in that)
 */        
    function getSettlements($country, $region=null, $subregion=null, $place=null, $network=null, $streets=null, $buildings=null, $imagery=null, $addresses=null, $amenities=null, $paths=null, $mapillary=null)
    {
        $query = "SELECT id_country, id_settlement, id_area, name, name_en, lat, lon, region, subregion, place, capital,
            network, streets, buildings, imagery, addresses, amenities, paths, mapillary
            FROM 
                (select `settlements`.`id` AS `id`,`settlements`.`id_country` AS `id_country`,`settlements`.`osm_id` AS `osm_id`,
                `settlement_areas`.`id` AS `id_area`,`settlement_areas`.`id_settlement` AS `id_settlement`,`settlement_areas`.`area_name` AS `area_name`,
                `settlement_areas`.`network` AS `network`,`settlement_areas`.`streets` AS `streets`,`settlement_areas`.`buildings` AS `buildings`,
                `settlement_areas`.`imagery` AS `imagery`,`settlement_areas`.`addresses` AS `addresses`,`settlement_areas`.`amenities` AS `amenities`,
                `settlement_areas`.`paths` AS `paths`,`settlement_areas`.`mapillary` AS `mapillary`,`settlements`.`name` AS `name`,
                `settlements`.`name_en` AS `name_en`,`settlements`.`lat` AS `lat`,`settlements`.`lon` AS `lon`,`settlements`.`region` AS `region`,
                `settlements`.`subregion` AS `subregion`,`settlements`.`place` AS `place`,`settlements`.`capital` AS `capital`,`settlements`.`wikidata` AS `wikidata`,
                `settlements`.`wikipedia` AS `wikipedia` from (`settlements` join `settlement_areas`) where (`settlements`.`id` = `settlement_areas`.`id_settlement`)) z
            WHERE area_name is null
            AND id_country = :country";
        if ($region != null && $region <> "[no name]") { $query .= " AND region = :region"; }
        if ($region == "[no name]") { $query .= " AND (region = '' OR region is null)"; }
        if ($subregion != null) { $query .= ' AND subregion = :subregion'; }
        if ($place != null) { $query .= " AND place= :place"; }
        if ($network != null) { $query .= " AND network = :network"; }
        if ($streets != null) { $query .= " AND streets = :streets"; }
        if ($buildings != null) { $query .= " AND buildings = :buildings"; }
        if ($imagery != null) { $query .= " AND imagery = :imagery"; }
        if ($addresses != null) { $query .= " AND addresses = :addresses"; }
        if ($amenities != null) { $query .= " AND amenities = :amenities"; }
        if ($paths != null) { $query .= " AND paths = :paths"; }
        if ($mapillary != null) { $query .= " AND mapillary = :mapillary"; }
        $query .= " ORDER BY if(name <> '',0,1),name ASC, lat asc";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':country', $country);
        if ($region != null && $region <> "[no name]") { $stmt->bindParam(':region', $region); }
        if ($subregion != null) { $stmt->bindParam(':subregion', $subregion); }
        if ($place != null) { $stmt->bindParam(':place', $place); }
        if ($network != null) { $stmt->bindParam(':network', $network); }
        if ($streets != null) { $stmt->bindParam(':streets', $streets); }
        if ($buildings != null) { $stmt->bindParam(':buildings', $buildings); }
        if ($imagery != null) { $stmt->bindParam(':imagery', $imagery); }
        if ($addresses != null) { $stmt->bindParam(':addresses', $addresses); }
        if ($amenities != null) { $stmt->bindParam(':amenities', $amenities); }
        if ($paths != null) { $stmt->bindParam(':paths', $paths); }
        if ($mapillary != null) { $stmt->bindParam(':mapillary', $mapillary); }
        $stmt->execute();
        $settles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalrows = $stmt->RowCount;
        $zindex = ($totalrows * 8) + 20;
        
        echo "<table class='setlist' id='jardir'>";
        $edgerow = "<tr class='edgerow'><th colspan=4></th>";
        $edgerow .= "<th colspan=4>Remote mapping</th>";
        $edgerow .= "<th colspan=4>Local mapping</th>";
        $edgerow .= "<th></th>";
        $edgerow .= "</tr>";
        
        $headrow .= "<tr>";
        $headrow .= "<th>".$this->placeDropdown($country, $region, $subregion, $place)."</th>";
        $headrow .= "<th>"._name."</th>";
        $headrow .= "<th>"._location."</th><th>"._editOSM."</th>";
        //$headrow .= "<th>".$this->makeFilterField($zindex, "network", "n", $network)."</th>"; $zindex--;
        $headrow .= "<th><img src='/img/black/highway.png' title='" . _network . "'></th>";            
        $headrow .= "<th><img src='/img/black/streets.png' title='" . _streets . "'></th>";
        $headrow .= "<th><img src='/img/black/buildings.png' title='" . _buildings . "'></th>";
        $headrow .= "<th><img src='/img/black/cloudysunny.png' title='" . _imagery . "'></th>";
        $headrow .= "<th><img src='/img/black/addresses.png' title='" . _addresses . "'></th>";
        $headrow .= "<th><img src='/img/black/amenities.png' title='" . _amenities . "'></th>";
        $headrow .= "<th><img src='/img/black/paths.png' title='" . _paths . "'></th>";
        $headrow .= "<th><img src='/img/black/photo.png' title='" . _mapillary . "'></th>";
        $headrow .= "<th>".$this->subRegionDropdown($country, $region, $subregion)."</th>";
        
        //$headrow .= "<th>"._last_changed."</th><th>"._last_changed_by."</th>"
        $headrow .= "</tr>";
        
        echo $edgerow;
        echo $headrow;
        
        foreach($settles as $settle)
        {
            $name = $settle["name"];
            if ($name == "") { $name = "[no name]"; }
            if ($subregion == "")
            {
                $subregiontext = '<a href="?idc='.$country.'&region='.$region.'&sub='.$settle["subregion"].'">'.$settle["subregion"]."</a>";
            }
            else
            {
                $subregiontext = $settle["subregion"];
            }
            
            $thisrow = "<tr>";
            $thisrow .= '<td><label class="placetype">'.$settle["place"].'</label></td>';
            $thisrow .= '<td><label class="placename" title="'.$settle["name_en"].'"><a href="settlement.php?ids='.$settle["id_settlement"].'">'.$name.'</a></label></td>';
            $thisrow .= "<td>".$this->osmLink($settle["lat"], $settle["lon"])."</td>\n";
            $thisrow .= "<td>".$this->osmLinkEdit($settle["lat"], $settle["lon"])."</td>";
            $thisrow .= $this->makeField($zindex, "network",$settle["id_area"], $settle["name"], $settle["network"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "streets",$settle["id_area"], $settle["name"], $settle["streets"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "buildings",$settle["id_area"], $settle["name"], $settle["buildings"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "imagery",$settle["id_area"], $settle["name"], $settle["imagery"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "addresses",$settle["id_area"], $settle["name"], $settle["addresses"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "amenities",$settle["id_area"], $settle["name"], $settle["amenities"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "paths",$settle["id_area"], $settle["name"], $settle["paths"]);
            $zindex--;
            $thisrow .= $this->makeField($zindex, "mapillary",$settle["id_area"], $settle["name"], $settle["mapillary"]);
            $zindex--;
            $thisrow .= '<td class="subregionname">'.$subregiontext.'</td>';
            $thisrow .= "</tr>\n\n";
            echo $thisrow;
        }
        echo $headrow;
        echo $edgerow;
        echo "</table>";
    }
    
    function getOne($ids)
    {
      
        $query = "SELECT id_country, osm_id, id_settlement, id_area, network, streets, buildings, imagery, addresses, amenities, paths, mapillary, "
                . "name, name_en, lat, lon, region, subregion, place, capital, wikidata, wikipedia "
                . "FROM (select `settlements`.`id` AS `id`,`settlements`.`id_country` AS `id_country`,`settlements`.`osm_id` AS `osm_id`,
                `settlement_areas`.`id` AS `id_area`,`settlement_areas`.`id_settlement` AS `id_settlement`,`settlement_areas`.`area_name` AS `area_name`,
                `settlement_areas`.`network` AS `network`,`settlement_areas`.`streets` AS `streets`,`settlement_areas`.`buildings` AS `buildings`,
                `settlement_areas`.`imagery` AS `imagery`,`settlement_areas`.`addresses` AS `addresses`,`settlement_areas`.`amenities` AS `amenities`,
                `settlement_areas`.`paths` AS `paths`,`settlement_areas`.`mapillary` AS `mapillary`,`settlements`.`name` AS `name`,
                `settlements`.`name_en` AS `name_en`,`settlements`.`lat` AS `lat`,`settlements`.`lon` AS `lon`,`settlements`.`region` AS `region`,
                `settlements`.`subregion` AS `subregion`,`settlements`.`place` AS `place`,`settlements`.`capital` AS `capital`,`settlements`.`wikidata` AS `wikidata`,
                `settlements`.`wikipedia` AS `wikipedia` from (`settlements` join `settlement_areas`) where (`settlements`.`id` = `settlement_areas`.`id_settlement`)) z "
                . "WHERE id_settlement=:ids AND area_name IS NULL";
       
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':ids', $ids);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    }
   
    function makeFilterField($zindex, $type, $name, $datavalue)
    {
        $typeindex = $type.$zindex;
        if (is_null($datavalue)) {
            $datavalue = "";
        }
        $returner .= "<th><div class='fixer' onClick=\"toggleDropdown('".$typeindex."','dropdown-4')\">";
        $returner .= "<div class='image-dropdown dropdown-min' style='z-index:$zindex;' id='$typeindex' ><label class='closer'>[x]</label>";

        //header
        $returner .= '<input type="radio" id="'.$typeindex . 'h" name="'.$typeindex . '" onClick="toggleDropdown(\''.$typeindex.'\', \'dropdown-4\')" value="" disabled>'
                    . ' <label class="'.$type.'title" for="'.$typeindex . 'h"></label>';

        $returner .= $this->makeFilterFieldEntry($name, "a", $type, $id, $datavalue, "100", $this->textField($type, "100"), 4);
        $returner .= $this->makeFilterFieldEntry($name, "b", $type, $id, $datavalue, "50", $this->textField($type, "50"), 4);
        $returner .= $this->makeFilterFieldEntry($name, "c", $type, $id, $datavalue, "0", $this->textField($type, "0"), 4);
        $returner .= $this->makeFilterFieldEntry($name, "d", $type, $id, $datavalue, "null", $this->textField($type, "null"), 4);

        $returner .= "</div></div></th>\n";
        return $returner;
    }
    
    function makeFilterFieldEntry($typeindex, $extra, $type, $id, $datavalue, $value, $text, $noptions)
    {
        $radioid = $typeindex.$extra;
        $returner = "<input type='radio' id='".$radioid.$value."' name='".$typeindex."' value='".$value."'";
        if ($datavalue == $value) {
            $returner .= " checked";
        }
        $returner .= " onclick=\"iconFilter(" . $typeindex . ", $value)\" />";
        $returner .= '<label class="'.$type.''.$value.'" for="'.$radioid.''.$value.'"><span>'.$text.'</span></label>';
        return $returner;
    }
    
    function makeField($zindex, $type, $id, $name, $datavalue)
    {
        $typeindex = $type.$zindex;
        if (is_null($datavalue)) {
            $datavalue = "null";
        }
        $returner .= "<td><div class='fixer' onClick=\"toggleDropdown('".$typeindex."','dropdown-4')\">";
        $returner .= "<div class='image-dropdown dropdown-min' style='z-index:$zindex;' id='$typeindex' ><label class='closer'>[x]</label>";

        //header
        $returner .= '<input type="radio" id="'.$typeindex . 'h" name="'.$typeindex . '" onClick="toggleDropdown(\''.$typeindex.'\', \'dropdown-4\')" value="" disabled>'
                    . ' <label class="'.$type.'title" for="'.$typeindex . 'h"><span>' . $name . '</span></label>';

        $returner .= $this->makeFieldEntry($typeindex, "a", $type, $id, $datavalue, "100", $this->textField($type, "100"), 4);
        $returner .= $this->makeFieldEntry($typeindex, "b", $type, $id, $datavalue, "50", $this->textField($type, "50"), 4);
        $returner .= $this->makeFieldEntry($typeindex, "c", $type, $id, $datavalue, "0", $this->textField($type, "0"), 4);
        $returner .= $this->makeFieldEntry($typeindex, "d", $type, $id, $datavalue, "null", $this->textField($type, "null"), 4);

        $returner .= "</div></div></td>\n";
        return $returner;
    }
    
    function makeFieldJardir($zindex, $type, $id, $name, $datavalue)
    {
        $typeindex = $type.$zindex;
        if (is_null($datavalue)) {
            $datavalue = "null";
        }
        $returner .= "<td><div class='fixer' onClick=\"toggleDropdown('".$typeindex."','dropdown-3')\">";
        $returner .= "<div class='image-dropdown dropdown-min' style='z-index:$zindex;' id='$typeindex' ><label class='closer'>[x]</label>";

        //header
        $returner .= '<input type="radio" id="'.$typeindex . 'h" name="'.$typeindex . '" onClick="toggleDropdown(\''.$typeindex.'\', \'dropdown-3\')" value="" disabled>'
                    . ' <label class="'.$type.'title" for="'.$typeindex . 'h"><span>' . $name . '</span></label>';

        $returner .= $this->makeFieldEntry($typeindex, "a", $type, $id, $datavalue, "1", $this->textField($type, "100"), 3);
        $returner .= $this->makeFieldEntry($typeindex, "b", $type, $id, $datavalue, "0", $this->textField($type, "0"), 3);
        $returner .= $this->makeFieldEntry($typeindex, "c", $type, $id, $datavalue, "2", $this->textField($type, "null"), 3);

        $returner .= "</div></div></td>\n";
        return $returner;
    }
    
    function makeFieldEntry($typeindex, $extra, $type, $id, $datavalue, $value, $text, $noptions)
    {
        $radioid = $typeindex.$extra;
        $returner = "<input type='radio' id='".$radioid.$value."' name='".$typeindex."' value='".$value."'";
        if ($datavalue == $value) {
            $returner .= " checked";
        }
        $returner .= " onclick=\"radioClick(" . $id . ",'$type',$value, '$typeindex', 'dropdown-$noptions')\" />";
        $returner .= '<label class="'.$type.''.$value.'" for="'.$radioid.''.$value.'"><span>'.$text.'</span></label>';
        return $returner;
    }
    
    function trailMaker($country, $region, $subregion)
    {
        $link = "settlements.php";
        $trail = "<div class='trail'><a href='$link'>"._settlements."</a>";
        if ($country != "")
        {
            $countryname = $this->getCountryName($country);
            $link .= "?idc=$country";
            $trail .= " &gt; <a href='$link'>$countryname</a>";
        }
        if ($region != "")
        {
            $link .= "&region=$region";
            $trail .= ' &gt; <a href="'.$link.'">'.$region.'</a>';
        }
        if ($subregion != "")
        {
            $link .= "&sub=$subregion";
            $trail .= ' &gt; <a href="'.$link.'">'.$subregion.'</a>';
        }
        $trail .= "</div>";
        echo $trail;
    }
    
    function pageTitle($country, $region, $subregion)
    {
        $trail = _settlements;
        if ($country != "")
        {
            $countryname = $this->getCountryName($country);
            $trail .= " &gt; $countryname";
        }
        if ($region != "")
        {
            $trail .= " &gt; $region";
        }
        if ($subregion != "")
        {
            $trail .= " &gt; $subregion";
        }
        return $trail;
    }
    
    function subRegionDropdown($country, $region, $selected="")
    {
        $options = $this->subRegions($country, $region);
        
        $returner = "<form name='dropselector'><select id='subregs' name='subregion' onchange='toggleFilter(\"subregs\",\"sub\")'><option value=''>"._subdistrict."</option>\n";
        foreach ($options as $subregion)
        {
            $returner .= '<option value="'.urlencode($subregion).'" ';
            if ($selected == $subregion) { $returner .= "selected"; }
            $returner .= ">".$subregion."</option>\n";
        }
        $returner .= "</select></form>";
        
        return $returner;
    } 
    
    function placeDropdown($country, $region, $subregion, $selected="")
    {
        $options = $this->placeType($country, $region, $subregion);
        
        $returner = "<form name='dropselectorplace'><select id='placeType' name='place' onchange='toggleFilter(\"placeType\", \"p\")'><option value=''>"._placetype."</option>\n";
        foreach ($options as $place)
        {
            $returner .= '<option value="'.$place.'" ';
            if ($selected == $place) { $returner .= "selected"; }
            $returner .= ">".$place."</option>\n";
        }
        $returner .= "</select></form>";
        
        return $returner;
    } 
    
    function subRegions($country, $region)
    {
        $query = "SELECT DISTINCT subregion FROM v_settlement_areas WHERE id_country= :country AND region= :region AND subregion IS NOT NULL AND subregion <> '' ORDER BY subregion ASC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':region', $region);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function placeType($country, $region, $subregion)
    {
        $query = "SELECT DISTINCT place FROM v_settlement_areas WHERE id_country = :country AND region = :region";
        if ($subregion != "") { $query .= ' AND subregion = :subregion '; }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':region', $region);
        if ($subregion != "") { $stmt->bindParam(':subregion', $subregion); }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function traverseSettlements($idc, $ids, $region, $subregion="")
    {
        $query = "SELECT id_settlement, name, name_en FROM v_settlement_areas WHERE id_country = :idc AND region = :region";
        if ($subregion != "") { $query .= " AND subregion = :subregion "; }
        $query .= " ORDER BY if(name <> '',0,1),name ASC, lat asc";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $idc);
        $stmt->bindParam(':region', $region);
        if ($subregion != "") { $stmt->bindParam(':subregion', $subregion); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function getCountryName($country)
    {
        $query = "SELECT name_en FROM countries WHERE id = :country";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':country', $country);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
    
    // Closest within radius of 25 Miles - Haversine formula
    // 37, -122 are your current coordinates
    // To search by kilometers instead of miles, replace 3959 with 6371
    /*SELECT feature_name, 
     ( 6371 * acos( cos( radians(37) ) * cos( radians( lat ) ) 
      * cos( radians( long ) - radians(-122) ) + sin( radians(37) ) 
      * sin( radians( lat ) ) ) ) AS distance 
    FROM geo_features HAVING distance < 25 
    ORDER BY distance LIMIT 1;*/
    function closestSettlements($ids)
    {
        $stlm = $this->getOne($ids);
        
        $query = "SELECT id, name, name_en, region, subregion, 
          ( 6371 * acos( cos( radians(".$stlm["lat"].") ) * cos( radians( lat ) ) 
          * cos( radians( lon ) - radians(".$stlm["lon"].") ) + sin( radians(".$stlm["lat"].") ) 
          * sin( radians( lat ) ) ) ) AS distance 
          FROM settlements 
          WHERE id_country = ".$stlm["id_country"]. "
          AND id != ".$stlm["id_settlement"]."
          HAVING distance > 0 
          ORDER BY distance LIMIT 3";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*
     * For LESOTHO project
     */
    
     function closestSettlementsLesotho($ids)
    {
        $stlm = $this->getOne($ids);
        
        $query = "SELECT vill_code, village, lon, lat, 
          ( 6371 * acos( cos( radians(".$stlm["lat"].") ) * cos( radians( lat ) ) 
          * cos( radians( lon ) - radians(".$stlm["lon"].") ) + sin( radians(".$stlm["lat"].") ) 
          * sin( radians( lat ) ) ) ) AS distance 
          FROM lesotho_villages 
          HAVING distance < 5 
          ORDER BY distance LIMIT 3";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    function textField($type, $value)
    {
        if (is_null($value) or $value == "null")
        {
            return "Unknown";
        }
        
        if ($type === "network" || $type === "veglagt")
        {
            switch ($value) {
            case "100":
                return "Connected to road network";
            case "50":
                return "Connected via airport/ferry";
            case "0":
                return "Not connected to road network";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "streets")
        {
            switch ($value) {
            case "100":
                return "All streets on map";
            case "50":
                return "Partial street coverage";
            case "0":
                return "No streets on map";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "buildings" || $type === "byggingar")
        {
            switch ($value) {
            case "100":
                return "All visible buildings on map";
            case "50":
                return "Some buildings on map";
            case "0":
                return "No buildings mapped";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "imagery" || $type == "loftmynd")
        {
            switch ($value) {
            case "100":
                return "Good imagery";
            case "50":
                return "Some clouds or resolution problems";
            case "0":
                return "Unusable imagery (clouds/resolution)";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "addresses")
        {
            switch ($value) {
            case "100":
                return "Good quality";
            case "50":
                return "Some addresses/street names on map";
            case "0":
                return "No addresses/street names on map";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "amenities")
        {
            switch ($value) {
            case "100":
                return "Most amenities listed (education, health, etc)";
            case "50":
                return "Some amenities listed (education, health, etc)";
            case "0":
                return "No amenities on map";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "paths")
        {
            switch ($value) {
            case "100":
                return "Paths on map (pedestrian, bicycle)";
            case "50":
                return "Some paths on map (pedestrian, bicycle)";
            case "0":
                return "No paths on map";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "mapillary")
        {
            switch ($value) {
            case "100":
                return "Excellent Mapillary coverage";
            case "50":
                return "Some Mapillary images";
            case "0":
                return "No Mapillary images";
            default:
                return "Unknown";
            }
        }
        
        if ($type == "kortlagt")
        {
            switch ($value) {
                case "100":
                    return "Name on map";
                case "0":
                    return "Name is not on map";
                default:
                    return "Unknown";
            }  
        }
    }

    function regionOverview($id_country)
    {
       //$db = clone $this->sql;
       
       $query = "SELECT DISTINCT region, count(*) AS numset FROM settlements WHERE id_country=:idc GROUP BY region ORDER BY region";
       //$db->Query($query);
       
       $stmt = $this->pdo->prepare($query);
       $stmt->bindParam(':idc', $id_country);
       $stmt->execute();
       $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       echo "<table class='districtOverview'>";
       echo "<tr><th>Region</th><th>Settlements</th></tr>";
       //while ($db->ReadRow()) 
       foreach($data as $item)
       {
           $distname = trim($item["region"]);
           if ($distname == "") { $distname = "[no name]"; }
           $distlink = '<a href="?idc='.$id_country.'&region='.$distname.'">'.$distname.'</a>';
           echo "<tr><td>$distlink</td><td>".$item["numset"]."</td></tr>";
       }
       echo "</table>";
    }
    
    function countryOverview()
    {
        $query = "select `countries`.`id` AS `id`,`countries`.`name` AS `name`,`countries`.`name_en` AS `name_en`,count(0) AS `setnum` from (`countries` join `settlements`) where (`countries`.`id` = `settlements`.`id_country`) group by `countries`.`id`,`countries`.`name`,`countries`.`name_en` order by `countries`.`name_en`";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
       echo "<table class='countryOverview'>";
       echo "<tr><th>Country</th><th>Settlements</th></tr>";
       foreach ($data as $item) 
       {
           $distlink = "<a href='?idc=".$item["id"]."'>".$item["name_en"]."</a>";
           echo "<tr><td>$distlink</td><td>".$db->RowData["setnum"]."</td></tr>";
       }
       echo "</table>";
        
        
    }

//updates value in table settlement_areas
    function updateField($id, $field, $value) {
        try {
            if (strlen($this->osmUser) > 0) {
                //timestamp
                $now = time() - date('Z');
                $today = date("Y-m-d", $now);
                $klukka = date("H:i:s", $now);
                $now = $today . " " . $klukka;

                //action
                $query = "UPDATE settlement_areas SET $field=:value WHERE id=:id";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                //log
                $query2 = "INSERT INTO log_actions (osm_user, the_datetime, item_table, item_id, item_field, item_value, item_query) "
                        . "VALUES ('$this->osmUser', '$now', 'settlement_areas', :id, :field, :value, '$query')";
                $stmt = $this->pdo->prepare($query2);
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':field', $field);
                $stmt->execute();
                return 1;
            }
        } catch (Exception $e) {
            return 0;
        }
    }
   	
    /*leitarfunction*/
    function searchSettlement($term)
    {
        $query = "SELECT id_settlement, name, name_en, countryname, countryname_en, region, subregion, place, lat, lon ".
                "FROM v_settlements_countries ".
                "WHERE area_name IS NULL ".
                "AND (name like '%' || :term || '%' ".
                "OR name_en like '%' || :term || '%')";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':term', $term);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function josmLink($lat, $lon, $josmmodifier=0.01)
    {
        $josm_top = round($lat,2)+($josmmodifier/2);
        $josm_bottom = round($lat,2)-($josmmodifier/2);
        $josm_left = round($lon,2)-$josmmodifier;
        $josm_right = round($lon,2)+$josmmodifier;

        return  "<a href='http://127.0.0.1:8111/load_and_zoom?left=$josm_left&right=$josm_right&top=$josm_top&bottom=$josm_bottom' target='_new'>"._editJOSM."</a>";
    }
    
    function osmLink($lat, $lon)
    {
        return "<a href='http://www.openstreetmap.org/?mlat=".$lat."&mlon=".$lon."#map=15/".$lat."/".$lon."' target='_blank'>".number_format(round($lat, 2), 2)." ".number_format(round($lon, 2), 2)."</a>";
    }
    
    function osmLinkEdit($lat, $lon)
    {
        if($this->osmEditor == "1")
        {
            return $this->josmLink($lat, $lon);
        }
        else
        {
            return "<a href='http://www.openstreetmap.org/edit#map=17/".$lat."/".$lon."' target='_blank' title='".$lat." ".$lon."'>"._editOSM."</a>";
        }
    }

    /*
     * Gets a random place to reveiew imagery for
     * Can be filtered for a particular country, region or subregion
     */
    function imageryReview($id_country="", $region="", $subregion="")
    {
        $query = "SELECT * FROM (";
        $query .= "SELECT id_settlement, id_area, name, name_en, lat, lon, region, subregion, place, id_country FROM v_settlement_areas";
        $query .= " WHERE imagery IS NULL";
        if ($id_country != "") { $query .= " AND id_country=:idc"; }
        if ($region != "") { $query .= " AND region=:reg"; }
        if ($subregion != "") { $query .= " AND subregion=:subreg"; }
        $query .= " LIMIT 100) x ORDER BY rand()";
        $query .= " LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        if ($id_country != "") { $stmt->bindParam(':idc', $id_country); }
        if ($region != "") { $stmt->bindParam(':reg', urldecode($region)); }
        if ($subregion != "") { $stmt->bindParam(':idc', urldecode($subregion)); }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            
        if ($data != null)
        {
            //$db->ReadRow();
            $returner = $data;
        }
        else
        {
            $returner = array("Error" => "yes");
        }
        return $returner;
    }
    
    //needs tuning
    function getActivity($ids)
    {
        $query = "select z.name, z.name_en, z.region, z.subregion, z.place, z.the_datetime,
            z.osm_user, z.id_settlement as item_id, z.item_table, c.name as countryname, c.name_en as countryname_en FROM
            (select s.name, s.name_en, s.region, s.subregion, s.place, s.id_country, y.the_datetime, y.osm_user, y.id_settlement, y.item_table
            FROM 
            (
            Select x.the_datetime, x.item_table, x.item_id, x.osm_user, sa.id_settlement
            FROM 
            (
            SELECT MAX(a.the_datetime) as the_datetime, a.item_table, a.item_id, a.osm_user
            FROM log_actions a
            WHERE a.item_table='settlement_areas'       
            ORDER BY the_datetime DESC LIMIT 50
                )  x, settlement_areas sa
            where x.item_id=sa.id ) y, settlements s
            where y.id_settlement= s.id
            AND y.id_settlement=:ids) z,
            countries c
            where c.id = z.id_country";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':ids', $ids);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function countriesMissingImagery()
    {
        $query = "SELECT name_en, id, last_generated FROM cache_imagery_picker";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function updateCountriesMissingImagery()
    {
        $now = time() - date('Z');
        $today = date("Y-m-d", $now);
        $klukka = date("H", $now);
        $now = $today . " " . $klukka;
        $query = "truncate table cache_imagery_picker;";
        $db->Query($query);
        
        $query = "insert into cache_imagery_picker (name_en, id, last_generated) 
                select distinct c.name_en, c.id, '$now' from countries c, v_settlement_areas v
                where v.imagery is null
                and c.id=v.id_country
                order by name_en asc";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }
}

 