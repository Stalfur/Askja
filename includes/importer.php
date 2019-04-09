<?php

/**
 * Description of importer
 *
 * @author Jói
 */
class osm_api {

    public $url = "http://overpass-api.de/api/interpreter"; //default value
    public $urlnom = "http://nominatim.openstreetmap.org/reverse?format=xml&addressdetails=1&accept-language=xx&email=joi@betra.is";
    public $urlgc = "http://api.opencagedata.com/geocode/v1/xml?key=a14cd69b21e9ccc54c3343bdbc07ebf3&q="; //lat+lng
    public $urlmapillary = "https://a.mapillary.com/";
    //public $mapillaryClient = "dml2ZTVEVjYwRnZsSUFiZkxfZ0R0dzpmZmY4Y2Y5YmQ3YjhjOWY0";
    public $mapillaryClient = "dml2ZTVEVjYwRnZsSUFiZkxfZ0R0dzo0ODdkMGY3NjY4OGJiN2Q4";
    public $mapillarySecret = "NGE4NmYyNTBkZWE0MzcyNzc4ZmQwMTJlODA3ZTQ2NGE=";
    
    
    public $osmUser;
    public $pdo;

    public function setUser($name) {
        $this->osmUser = $name;
    }

    public function setPDO($tsql) {
        $this->pdo = $tsql;
    }   
    public function setUrl($newurl) {
        $this->url = $newurl;
    }
    
    public function setUrlNom($newurl) {
        $this->urlnom = $newurl;
    }

    //curl for reading remote data
    public function remote_api($url,$https="no") {
        $ch = curl_init();
        $timeout = 5;
        if ($https == "yes")
        {
            curl_setopt($ch, CURLPROTO_HTTPS, true);
            curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        }        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    //updates value in table settlement_areas  -- copied from class.settlements.php !!
    //only handles int
    public function updateField($id, $field, $value) {
        try {
            if (strlen($this->osmUser) > 0) {
                //timestamp
                $now = time() - date('Z');
                $today = date("Y-m-d", $now);
                $klukka = date("H:i:s", $now);
                $now = $today . " " . $klukka;

                //action
                $value = intval($value);
                $query = "UPDATE settlement_areas SET $field=$value WHERE id=$id";
                $sql->Query($query);
                //log
                $query = addslashes($query);
                $value = addslashes($value);
                $query2 = "INSERT INTO log_actions (osm_user, the_datetime, item_table, item_id, item_field, item_value, item_query) VALUES ('$this->osmUser', '$now', 'settlement_areas', $id, '$field', '$value', '$query')";
                $sql->Query($query2);
                return true;
            }
            else
            {
                echo "no user";
            }
        } catch (Exception $e) {
            return FALSE;
        }
    }
}

class importer extends osm_api 
{
    function continents() {
        $oquery = 'node["place"="continent"];out;';

        $contents = $this->remote_api($this->url . "?data=" . $oquery);
        //$contents = file_get_contents("continents.xml");
        if ($contents == null) {
            echo "Error!";
        } else {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->loadXML($contents);
            $xpath = new DOMXPath($xml);
            
            $xquery = '//node/tag[@k="name"]';
            $nodes = $xpath->query($xquery);
            if ($nodes->length) {
                foreach ($nodes as $value) {
                    $dbquery = "insert into continents (name) value ('" . $value->getAttribute('v') . "');";
                    $stmt = $this->pdo->prepare($dbquery);
                    $stmt->execute();
                }
            } else {
                echo "not found";
            }
        }
    }

    //import countries
    function countries() {
        $oquery = 'node["place"="country"];out;';
        //$oquery = 'relation["admin_level"="2"];out;';

        $contents = $this->remote_api($this->url . "?data=" . $oquery);
        //$contents = file_get_contents("countries.xml");
        if ($contents == null) {
            echo "Error!";
        } else {
            $dom = simplexml_load_string($contents);
            $result = $dom->xpath("node");
            //$result = $dom->xpath("relation");

            $data = $this->nodeMerge($result);
        
            foreach ( $data as $current ) {
                $continent = $this->getContinent($current['keys']['is_in:continent']);
                $osm_id = $current['id'];
                $lat = $current['lat'];
                $lon = $current['lon'];
                $name = $current['keys']['name'];
                $name_en = $current['keys']['name:en'];
                if ($name_en == "")
                {
                    $name_en = $name;
                }
                $int_name = $current['keys']['int_name'];
                $iso3166 = $current['keys']['ISO3166-1'];
                if ($iso3166 == "")
                {
                    $iso3166 = $current['keys']['country_code_iso3166_1_alpha_2'];
                }
                $dbquery = "insert into countries (osm_id, name, name_en, int_name, continent, iso3166, lat, lon) VALUES ";
                $dbquery .= "(:osm_id, :name, :name_en, :int_name, :continent, :iso3166, :lat, :lon)";
                
                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(':osm_id', $osm_id);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':name_en', $name_en);
                $stmt->bindParam(':int_name', $int_name);
                $stmt->bindParam(':continent', $continent);
                $stmt->bindParam(':iso3166', $iso3166);
                $stmt->bindParam(':lat', $lat);
                $stmt->bindParam(':lon', $lon);
                $stmt->execute();
                
            }
        }
    }
    
    //get regions for a particular country and populate settlements
    function settlementsRegions($id_country, $type="")
    {
        $query = "SELECT DISTINCT region FROM settlements WHERE id_country=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id_country);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    //import settlements for a particular country, type can be city/town/village/hamlet etc
    function settlements($id_country, $type, $region="")
    {
        $countryname = $this->getCountry($id_country);
        if ($countryname == "")
        {
            exit;
        }
        
        $oquery = 'area["name:en"="'.$countryname.'"];(node[place="'.$type.'"](area););out;';
        
        if ($region != "")
        {
            $oquery = 'area["name"="'.$region.'"];(node[place="'.$type.'"](area););out;';
        }
            
        $contents = $this->remote_api($this->url . "?data=" . urlencode($oquery));
                
        if ($contents == null) {
            echo "Error!";
            exit;
        } else {
            $dom = simplexml_load_string($contents);
            $result = $dom->xpath("node");

            $data = $this->nodeMerge($result);
            
            foreach ( $data as $current ) {
                $osm_id = $current['id'];
                $lat = $current['lat'];
                $lon = $current['lon'];
                $name = $current['keys']['name'];
                $name_en = $current['keys']['name:en'];
                $place = $current['keys']['place'];
                $capital = $current['keys']['capital'];
                if ($capital == "")
                {
                    $capital = $current['keys']['is_capital'];
                }
                $wikidata = $current['keys']['wikidata'];
                $wikipedia = $current['keys']['wikipedia'];
                
                //$dbquery = "insert into settlements (id_country, osm_id, name, name_en, lat, lon, place, capital, wikidata, wikipedia) VALUES ";
                $dbquery = "insert into settlements_rerun (id_country, osm_id, name, name_en, lat, lon, place, capital, wikidata, wikipedia) VALUES ";
                $dbquery .= "(:id_country, :osm_id, :name, :name_en, :lat, :lon, :place, :capital, :wikidata, :wikipedia)";
                $stmt = $this->pdo->prepare($dbquery);
                $stmt->bindParam(':id_country', $id_country);
                $stmt->bindParam(':osm_id', $osm_id);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':name_en', $name_en);
                $stmt->bindParam(':lat', $lat);
                $stmt->bindParam(':lon', $lon);
                $stmt->bindParam(':place', $place);
                $stmt->bindParam(':capital', $capital);
                $stmt->bindParam(':wikidata', $wikidata);
                $stmt->bindParam(':wikipedia', $wikipedia);
                $stmt->execute();
            }
        }
    }
    
    //creates a multi-array to store node and key attributes
    function nodeMerge($input)
    {
        return
        array_map(function(SimpleXMLElement $element) 
        {
            // parse child nodes, execute a closure on every element that matches our previous filter
            $mapping = array_map(function(SimpleXMLElement $child) {

                // create a array entry with k-attribute as key and v-attribute as value and return it.
                $data = array(
                    (string) $child->attributes()->k => (string) $child->attributes()->v
                );

                return $data;
            }, $element->xpath('./tag'));

            // convert attributes to an array
            $attributes = $element->attributes();
            $attributes = array_map('strval', iterator_to_array($attributes));

            // merge all sub arrays to one array level and attach it as tag-entry to the attributes
            $attributes['keys'] = call_user_func_array('array_merge', $mapping);

            return $attributes;
        }, $input);
    }
    
    //cache nominatim for all settlements within a country
    function loadCountryNominatim($id_country, $missing = "")
    {
        $query = "SELECT id FROM settlements WHERE id_country=:id_country";
        if ($missing != "" )
        {
            $query .= " AND id in (SELECT id FROM settlement_nominatim WHERE id_country=:id_country and id_settlement is null)";
        }
        $stmt = $this->pdo->prepare($dbquery);
        $stmt->bindParam(':id_country', $id_country);
        $stmt->execute();        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result as $item) 
        {
            $this->nominatim($item["id"]);
            sleep(1.0); //we don't want to make the Nominatim gods angry - this can be bypassed if we install a local nominatim instance
        }
    }
    
    //cache nominatim for a single settlement
    function nominatim($id_settlement)
    {
        $latlon = $this->getLatLon($id_settlement);

        $contents = $this->remote_api($this->urlnom . "&lat=" .$latlon["lat"] . "&lon=" . $latlon["lon"]);
        echo $latlon["osm_id"]."\n";
        //see if we got blocked by nominatim
        if (substr(trim($contents),0,6) != "<html>")
        {
            $query = "INSERT INTO nominatim_xml (id_settlement, created, nominatim) VALUES ($id_settlement, now(), '$contents') ";
            $query .= "ON CONFLICT (id_settlement) DO UPDATE SET created=now(), nominatim='$contents'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();   
        }
        else
        {
            return false;
        }
    }
    
    //update region/subregion with rules
    function nominatimByRules($id_country, $isempty = "")
    {
        $query = "SELECT rule_type, xmlfield, datafield, old_value, new_value FROM nominatim_rules WHERE id_country=:id_country ORDER by priority ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_country', $id_country);
        $stmt->execute();      
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result as $item) 
        {
            echo "<h2>".$item["rule_type"]."</h2>";
            if ($item["rule_type"] == "direct_map")
            {
                $this->updateCountryRegion($id_country, $item["xmlfield"], $item["datafield"]);
            }
            
            if ($db->RowData["rule_type"] == "replace")
            {
                $this->replaceRegion($id_country, $item["datafield"], $item["old_value"], $item["new_value"]);
            }
        }
    }
    
    //update region/subregion for an entire country
    function updateCountryRegion($id_country, $xmlfield, $datafield)
    {
        $query = "SELECT id FROM settlements WHERE id_country=:id_country AND ($datafield is null OR $datafield = '')";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_country', $id_country);
        $stmt->execute();      
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result as $item) 
        {
            $this->nominatimLookup($item["id"], $xmlfield, $datafield);
        }
    }
    
    //replace value for region/subregion for an entire country
    function replaceRegion($id_country, $datafield, $old_value, $new_value)
    {
        $query = "UPDATE settlements SET $datafield='".mysql_real_escape_string($new_value)."' ";
        $query .= "WHERE $datafield='".mysql_real_escape_string($old_value)."' AND id_country=:id_country";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id_country', $id_country);
        $stmt->execute();      
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    //update empty region/subregion with direct mapping for a single settlement
    function nominatimLookup($id_settlement, $xmlfield, $datafield, $counter = 0)
    {
        $query = "SELECT nominatim FROM nominatim_xml WHERE id_settlement = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id_settlement);
        $stmt->execute();      
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $nominatim = "";
        
        if ($result != null) {
            $nominatim = $result["nominatim"];
        }
        else
        {
            //we did not find this settlement or it was empty in nominatim table, we do one extra lookup
            /*
             $this->nominatim($id_settlement);
             if ($counter < 1)
             {
                $this->nominatimLookup($id_settlement, $xmlfield, $datafield, 1);
             }
             else
             {
                 exit;
             }
             * 
             */
        }
        
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = FALSE;
        $xml->loadXML($nominatim);
        $field = $xml->getElementsByTagName($xmlfield);
        if ($field != "")
        {
            foreach ($field as $f) {
                $value = $f->nodeValue;
            }
            if ($value != "")
            {
                $nquery = "UPDATE settlements SET ".$datafield." = :value WHERE id=:id";
                echo $nquery."\n";
                $stmt = $this->pdo->prepare($nquery);
                $stmt->bindParam(':id', $id_settlement);
                $stmt->bindParam(':value', $value);
                $stmt->execute();      
            }
        }
    }
    
    //get place=continent from Overpass
    function getContinent($name)
    {
        $query = "SELECT id FROM continents WHERE name = :name";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();      
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
    
    function getUnmappedCountries()
    {
        $query = "select c.id, co.name, c.name_en
            from countries c, continents co
            where c.id not in (select id_country from settlements)
            and c.continent=co.id
            order by co.name, c.id";
        //and c.continent = co.id
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();      
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function nominatimCountryCheck()
    {        
        $this->setUser(OSM_ROBOT_USER);
        $query = "SELECT DISTINCT id_country FROM settlements WHERE region IS NULL OR region = ''";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();      
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($data != null) {
            foreach ($data as $item) 
            {
                $this->loadCountryNominatim($item["id_country"], "yes");
                $this->nominatimByRules($item["id_country"]);
            }
         }
         else
         {
             return "nothing";
         }
    }

    //get countryname for Overpass query
    function getCountry($id)
    {
        $query = "SELECT name_en FROM countries WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();      
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    //get latlon for a particular settlement
    function getLatLon($id)
    {
        $query = "SELECT lat, lon, osm_id FROM settlements WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();      
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function populateAreas($id)
    {
        $query = "INSERT INTO settlement_areas (id_settlement) SELECT id FROM settlements WHERE id_country=:id";
        /*INSERT INTO settlement_areas (id_settlement) 
        SELECT s.id FROM settlements s 
        INNER JOIN settlement_areas sa ON
        s.id = sa.id_settlement
        WHERE sa.id_settlement is null
        and s.id_country=216
        */
        //rewrite - very slow
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();      
    }
    
    function resetNominatim($id="")
    {
        //$query = "update nominatim_xml set nominatim_xml.nominatim = null where nominatim_xml.id_settlement in (SELECT id FROM settlements where id_country=$id)";
        $query = "delete from nominatim_xml WHERE nominatim like '%<html>%'";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();      
    }
    
    

    //import pubtest
    /*
    [out:xml][timeout:180];
    // fetch area “Telford and Wrekin” to search in
    {{geocodeArea:Telford and Wrekin}}->.searchArea;
    // gather results
    (
      // query part for: “amenity=pub”
      node["amenity"="pub"](area.searchArea);
      way["amenity"="pub"](area.searchArea);
      relation["amenity"="pub"](area.searchArea);
    );
    // print results
    out meta center;
    
     */
}

class automater extends osm_api
{
    function mapillaryCountryCheck($missing="yes")
    {
        $query = "SELECT distinct id_country FROM v_settlement_areas WHERE mapillary IS NULL";
        if ($missing == "no") { $query .= " OR mapillary=0"; }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($data != null) 
        {
            foreach($data as $item) 
            {
                $this->mapillaryCheck($item["id_country"], "", "", $missing);
            }
        }
        else
        {
            return "nothing";
        }
    }
    
    function overpassCountryCheck($action="insert",$missing="yes")
    {
        $query = "SELECT distinct id_country FROM v_settlement_areas WHERE network IS NULL";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($data != null) 
        {
            if ($action == "insert")
            {
                $this->resetOverpass();
            }
            foreach ($data as $item) 
            {
                if ($action == "insert")
                {
                    $this->overpassByCountry($item["id_country"], "", "", $missing);
                }
                elseif ($action == "update")
                {
                    $this->updateFromOverpassByRegion($item["id_country"], $missing);
                }
            }
        }
        else
        {
            return "nothing";
        }
        
    }
    
    function mapillaryCheck($idc, $region = "", $subregion = "", $nullonly = "no")
    {
        $this->setUser(OSM_ROBOT_USER);
        $query = "SELECT id_area, name, name_en, subregion, region, lat, lon, mapillary FROM v_settlement_areas "
                . "WHERE id_country=:idc AND area_name IS NULL ";
        if ($region != "") { $query .= " AND region=:region "; }
        if ($subregion != "") { $query .= " AND subregion = :subregion "; }
        $query .= " AND (mapillary IS NULL";
        if ($nullonly != "yes") { $query .= " OR mapillary = 0"; }
        $query .= ")";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $idc);
        if ($region != "") { $stmt->bindParam(':region', $region); }
        if ($subregion != "") { $stmt->bindParam(':subregion', $subregion); }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($data != null) {
            foreach ($data as $item) 
            {
                $thisname = $item["name"];
                if ($thisname == "") { $thisname = "[no name]"; }
                
                $contents = json_decode($this->mapillaryLookupClose($item["lat"], $item["lon"]), true);
                
                //{"more":false,"ims":[]}
                if ($contents != '{"more":false,"ims":[]}')
                {
                    echo $thisname." Mapillary YES\n";
                    $this->updateField($item["id_area"], "mapillary", 50);                    
                }
                else
                {
                    echo $thisname." Mapillary NO\n";
                    if ($item["mapillary"] == null)
                    {
                        $this->updateField($item["id_area"], "mapillary", 0);
                    }                        
                }
                sleep(1.0);
            }
         }
         else
         {
             echo "No settlements with null or 0 Mapillary images";
         }
         unset($db);
    }
    
    //get key of image from Mapillary if one exists within 500m
    function mapillaryLookupClose($lat, $lon)
    {
        //"v2/search/im/close?client_id=<CLIENT_ID>&lat=55.874973779667876&limit=1&lon=12.981805801391602&min_ca=140&max_ca=190"
        $url = $this->urlmapillary."/v3/images?client_id=$this->mapillaryClient&closeto=$lat,$lon&radius=500&page=1&per_page=10";
        echo $url;
        return $this->remote_api($url, "yes");
    }
    
    //ask for Mapillary status per settlement per region
    function mapillaryByRegion($idc, $nullonly)
    {
        $query = "SELECT DISTINCT region FROM settlements WHERE id_country=:idc";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $idc);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($data != null) {
            foreach ($data as $item) 
            {
                echo "\n\nRegion ".$item["region"]."\n";
                $this->mapillaryCheck($idc, $item["region"], "", $nullonly);
            }
        }
        else
        {
            echo "No regions!\n";
        }
    }
    
    //query overpass for highways, buildings, addresses, amenities and paths
    function overpassSettlement($ids)
    {
        $query1 = "SELECT lat, lon FROM settlements WHERE id=:ids";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':ids', $ids);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $lat = $data["lat"];
        $lon = $data["lon"];
        $distance = 500;
        
        $oquery = "[out:xml];(";
        $oquery .= "way[highway](around:$distance,$lat,$lon);";
        $oquery .= "way[\"amenity\"](around:$distance,$lat,$lon);";
        $oquery .= "node[\"amenity\"](around:$distance,$lat,$lon);";
        $oquery .= "way[\"building\"](around:$distance,$lat,$lon);";
        $oquery .= "node[\"addr:housenumber\"](around:$distance,$lat,$lon);";
        $oquery .= "way[\"path\"](around:$distance,$lat,$lon);";
		//$oquery .= "way[\"path\"](around:$distance,$lat,$lon);";
        //$oquery .= "(._;>;););";
        $oquery .= ');out%20tags;'; //ath urlencodd
        
        $contents = $this->remote_api($this->url . "?data=" . $oquery);
        //echo "\n".$this->url . "?data=" . $oquery."\n\n";
        //echo $contents;
        if (substr(trim($contents),0,6) != "<html>")
        {
            $query = "INSERT INTO overpass_xml (id_settlement, created, overpass) VALUES ($ids, now(), '".mysql_escape_string($contents)."') ";
            $query .= "ON DUPLICATE KEY UPDATE created=now(), overpass='".mysql_escape_string($contents)."'";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
        }
        else
        {
            return false;
        }
    }
    
    //cache overpass settlements within a country
    function overpassByCountry($id_country, $region="", $subregion="", $missing="")
    {
        $query = "SELECT id, name FROM settlements WHERE id_country=:idc";
        if ($region != "") { $query .= " AND region = :region"; }
        if ($subregion != "") { $query .= " AND subregion = :subregion"; }
        if ($missing != "" )
        {
            $query .= " AND id in (SELECT id FROM settlement_overpass WHERE id_country=:idc and id_settlement is null)";
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $id_country);
        if ($region != "") { $stmt->bindParam(':region', $region); }
        if ($subregion != "") { $stmt->bindParam(':subregion', $subregion); }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($data);
        $now = 1;
        
        foreach($data as $item) 
        {
            echo "Name: ".$item["name"]." $now of $total\n";
            $this->overpassSettlement($item["id"]);
            $now++;
            sleep(1.0);
        }
    }
    
    //cache Overpass result for each region of intended country
    function overpassByRegion($idc, $nullonly)
    {
        $query = "SELECT DISTINCT region FROM settlements WHERE id_country=:idc";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $idc);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($data) > 0) {
            foreach($data as $item) 
            {
                echo "\n\nRegion ".$item["region"]."\n";
                $this->overpassByCountry($idc, $item["region"], "", $nullonly);
            }
        }
        else
        {
            echo "No regions!\n";
        }
    }
    
    //spool through a country to update from Overpass
    function updateFromOverpassByRegion($idc, $missing="yes")
    {
        $query = "SELECT DISTINCT region FROM settlements WHERE id_country=:idc";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $idc);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($data) > 0) {
            foreach($data as $item)  
            {
                echo "\n\nRegion ".$item["region"]."\n";
                $this->updateFromOverPass($idc, $item["region"], "", $missing);
            }
        }
        else
        {
            echo "No regions!\n";
        }
    }
    
    //update where the overpass is newer than latest update of settlement
    function updateFromOverpassNewer()
    {
        $query = "SELECT sa.id_settlement
        FROM
                (select item_id, max(the_datetime) as last_updated from log_actions
                WHERE item_table='settlement_areas' GROUP BY item_id) la,
        settlement_areas sa,
        overpass_xml o
        WHERE
        la.last_updated < o.created
        and la.item_id=sa.id
        and sa.id_settlement=o.id_settlement
        and (sa.network is null OR sa.network=0 OR 
            sa.streets is null OR sa.streets=0 OR 
            sa.buildings is null OR sa.buildings=0 OR 
            sa.addresses is null OR sa.addresses=0 OR 
            sa.amenities is null OR sa.amenities=0 OR 
            sa.paths is null OR sa.paths=0)
		UNION
		SELECT sa.id_settlement
		FROM settlement_areas sa,
		overpass_xml o
		WHERE 
		sa.id_settlement=o.id_settlement
		AND (sa.network is null)";
		//OR sa.network = 0)";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->setUser(OSM_ROBOT_USER);
        
        if (count($data) > 0) {
            foreach ($data as $item) 
            {
                 $this->overpassCheck($item["id_settlement"]);
            }
        }
        else {
            return "nothing";
        }
    }
    
    //update country by region for overpass values
    function updateFromOverPass($idc, $region="", $subregion="", $missing="yes")
    {   
        $this->setUser(OSM_ROBOT_USER);
        
        $query = "SELECT id_settlement FROM v_settlement_areas "
                . "WHERE id_country=:idc AND area_name IS NULL ";
        if ($region != "") { $query .= " AND region=:region"; }
        if ($subregion != "") { $query .= " AND subregion=:subregion"; }
        if ($missing == "yes") { $query .= " AND network is null "; }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':idc', $idc);
        if ($region != "") { $stmt->bindParam(':region', $region); }
        if ($subregion != "") { $stmt->bindParam(':subregion', $subregion); }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);        
        
        if (count($data) > 0) {
            foreach ($data as $item) 
            {
                 $this->overpassCheck($item["id_settlement"]);
            }
        }
    }
    
    function resetOverpass($id="")
    {
        //$query = "update nominatim_xml set nominatim_xml.nominatim = null where nominatim_xml.id_settlement in (SELECT id FROM settlements where id_country=$id)";
        $query = "delete from overpass_xml WHERE overpass like '%<html>%'";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }
    
    //lookup overpass results for a settlement, if none found ask for it to run
    //if found then 
    function overpassCheck($ids)
    {
        $query = "SELECT a.name, a.id_area, o.overpass, a.buildings, a.network, a.streets, a.paths, a.amenities, a.addresses "
                . "FROM overpass_xml o, v_settlement_areas a "
                . "WHERE o.id_settlement=:ids AND a.id_settlement=o.id_settlement";
        //AND a.area_name IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':ids', $ids);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);        
        
        if (count($data) > 0)
        {
            echo $data["name"]."\n";
            // BUILDINGS only if they are 0 or null
            $nowfield = "buildings";
            if ($data[$nowfield] == null || $data[$nowfield] == 0)
            {
                //echo "checking buildings\n";
                $xmlvalue = $this->overpassField($data["overpass"], "building", "way");
                $this->checkField($nowfield, $data["id_area"], $data[$nowfield], $xmlvalue);
            }
            // PATHS            
            $nowfield = "paths";
            if ($data[$nowfield] == null || $data[$nowfield] == 0)
            {
                //echo "checking paths\n";
                $xmlvalue = $this->overpassField($data["overpass"], "highway", "way", "footway");
                $this->checkField($nowfield, $data["id_area"], $data[$nowfield], $xmlvalue);
                
                if ($xmlvalue == 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "path");
                    if ($nxmlvalue != $xmlvalue)
                    {
                        $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue);
                    }
                }
            }
            // STREETS            
            $nowfield = "streets";
            if ($data[$nowfield] == null || $data[$nowfield] == 0)
            {
                //echo "checking streets\n";
                $xmlvalue = $this->overpassField($data["overpass"], "highway", "way", "residential");
                $this->checkField($nowfield, $data["id_area"], $data[$nowfield], $xmlvalue);
                
                if ($xmlvalue == 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "living_street");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue, 100);
                    $xmlvalue = $nxmlvalue;
                }
            }
            // NETWORK            
            $nowfield = "network";
            if ($data[$nowfield] == null || $data[$nowfield] == 0)
            {
                //echo "checking network\n";
                //first we check motorway
                $xmlvalue = $this->overpassField($data["overpass"], "highway", "way", "trunk");
                $this->checkField($nowfield, $data["id_area"], $data[$nowfield], $xmlvalue, 100);
                
                //maybe it is trunk
                if ($xmlvalue == 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "trunk");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue, 100);
                    $xmlvalue = $nxmlvalue;
                }
                
                //maybe it is primary
                if ($xmlvalue == 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "primary");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue, 100);
                    $xmlvalue = $nxmlvalue;
                }
                
                //maybe it is secondary
                if ($xmlvalue == 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "secondary");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue, 100);
                    $xmlvalue = $nxmlvalue;
                }
                
                //maybe it is tertiary
                if ($xmlvalue == 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "tertiary");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue, 100);
                    $xmlvalue = $nxmlvalue;
                }
                               
                //maybe it is unclassified  --- ATTN - we update this even if it has not changed from red using ===
                if ($xmlvalue === 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "highway", "way", "unclassified");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue, 100);
                    $xmlvalue = $nxmlvalue;
                }
            }
            
            // AMENITIES
            $nowfield = "amenities";
            if ($data[$nowfield] == null || $data[$nowfield] == 0)
            {
                //echo "checking amenities\n";
                $xmlvalue = $this->overpassField($data["overpass"], "amenity", "way");
                $this->checkField($nowfield, $data["id_area"], $data[$nowfield], $xmlvalue);
                
                if ($xmlvalue === 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "amenity", "node");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue);
                }
            }
            // ADDRESSES
            $nowfield = "addresses";
            if ($data[$nowfield] == null || $data[$nowfield] == 0)
            {
                //echo "checking addresses\n";
                $xmlvalue = $this->overpassField($data["overpass"], "addr:housenumber", "way");
                $this->checkField($nowfield, $data["id_area"], $data[$nowfield], $xmlvalue);
                
                if ($xmlvalue === 0)
                {
                    $nxmlvalue = $this->overpassField($data["overpass"], "addr:housenumber", "node");
                    $this->checkField($nowfield, $data["id_area"], $xmlvalue, $nxmlvalue);
                }
            }
        }
    }
    
    function checkField($fieldname, $areaid, $dbvalue, $xmlvalue, $override="")
    {
        if ($xmlvalue == 50 && ($dbvalue == NULL OR $dbvalue == 0))
        {
            if ($override != "") { $xmlvalue = $override; }
            $this->updateField($areaid, $fieldname, $xmlvalue);                    
            //echo "$areaid $fieldname YES\n";                
        }
        else if ($xmlvalue == 0 && $dbvalue == null)
        {
            //echo "$areaid $fieldname NO\n";
            $this->updateField($areaid, $fieldname, $xmlvalue);
        }
    }
    
    function overpassField($contents, $tag, $osmtype, $value = "")
    {
        if ($contents == null) {
            echo "Error!";
        } else {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->loadXML($contents);
            $xpath = new DOMXPath($xml);
            
            $xquery = '//'.$osmtype.'/tag[@k="'.$tag.'"]';
            if ($value != "") 
            {
                $xquery .= '[@v="'.$value.'"]';
            }
            //echo $xquery."\n";
            $nodes = $xpath->query($xquery);
            if ($nodes->length) 
            {
                return 50;
            }
            else
            {
                return 0;
            }
        }       
    }
}