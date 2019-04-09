<?php
/*
1 - user logs in for the first time
2 - make md5hash from seed+osm_name+osm_id
3 - inserted into user table
4 - set md5hash as cookie upon logon
5 - if session expired and user is updating data - relog user in
	by making lookup from the cookie and renew his credentials from that
*/
class osmUser extends common
{
    function checkPreferences($osm_id)
    {
        $query = "SELECT osm_id FROM user_preferences WHERE osm_id=:osm_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':osm_id', $osm_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($result == null)
        {
            $query = "INSERT INTO user_preferences (osm_id, use_josm) VALUES (:osm_id, 0)";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':osm_id', $osm_id);
            $stmt->execute();
        }
    }

    function updatePreferences($osm_id, $use_josm)
    {
        $query = "UPDATE user_preferences SET use_josm=:use_josm WHERE osm_id=:osm_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':osm_id', $osm_id);
        $stmt->bindParam(':use_josm', $use_josm);
        $stmt->execute();
    }

    function getPreferences($osm_id)
    {
        $query = "SELECT use_josm FROM user_preferences WHERE osm_id=:osm_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':osm_id', $osm_id);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($result != null)
        {
            $returner .= "<table class='preferences'><tr><th>"._preferences."</th><th>"._value."</th><th>"._change."</th></tr>";
            foreach ($result as $item)
            {
                $use_josm = $item["use_josm"];
                $use_josm_value = _unknown;
                $flip_josm = 0;
                $josm_button = "";
                switch ($use_josm)
                {
                    case 0: 
                        $use_josm_value = _no; 
                        $flip_josm= 1; 
                        $josm_button = _useJOSM;
                        break;
                    case 1: 
                        $use_josm_value = _yes; 
                        $flip_josm= 0; 
                        $josm_button = _dontuseJOSM;
                        break;
                }
                $returner .= "<tr><td>"._useJOSM."</td><td>".$use_josm_value."</td><td>";
                $returner .= '<input type="button" value="'.$josm_button.'" class="submit" onClick="updPrefences('.$flip_josm.')">';
                $returner .= "</td></tr>";
            }
            $returner .= "</table>";
        }
        return $returner;
    }

    function getUserList()
    {
        $query = "SELECT osm_user, osm_id, MAX(the_datetime) AS last_login FROM log_users GROUP BY osm_user, osm_id ORDER BY osm_user";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function getUserActivity($osm_user="")
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
            WHERE a.item_table='settlement_areas' ";
        if ($osm_user <> "") { $query .= " AND osm_user = :osm_user "; }
        $query .= "GROUP BY a.item_table, a.item_id, a.osm_user
            ORDER BY the_datetime DESC LIMIT 50
                )  x, settlement_areas sa
            where x.item_id=sa.id ) y, settlements s
            where y.id_settlement= s.id) z,
            countries c
            where c.id = z.id_country";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':osm_user', $osm_user);
        $stmt->execute();        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function isUser($osm_user)
    {
        $query = "SELECT distinct osm_user FROM log_users WHERE osm_user= :$osm_user";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':osm_user', $osm_user);
        $stmt->execute();        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}