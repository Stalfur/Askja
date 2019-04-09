<?php
	ini_set('include_path', '../');
        include("includes/connect.php");
        $common = new common();
        $common->setPDO($dbh);
	
	
    if ($_POST["destroy"] == "yes")
    {
        $_SESSION["osm_user"] = null;
        $_SESSION["osm_id"] = null;
        session_destroy();
    }
    else
    {
	
	$display_name = $_REQUEST['display_name'];
	$the_id = $_REQUEST['id'];
	$the_ip = $_SERVER['REMOTE_ADDR'];
	$now = time() - date('Z');
        $today = date("Y-m-d",$now);
        $klukka = date("H:i:s", $now);
	$now = $today." ".$klukka;
	
	$_SESSION["osm_user"] = $display_name;
	$_SESSION["osm_id"] =  $the_id;
	
	$query = "INSERT INTO log_users (osm_user, osm_id, remote_ip, the_datetime) VALUES 
	(:disp, :the_id, :the_ip, :now)";
        
        $stmt = $common->pdo->prepare($query);
        $stmt->bindParam(':disp', $display_name);
        $stmt->bindParam(':the_id', $the_id);
        $stmt->bindParam(':the_ip', $the_ip);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        
	$query2 = "SELECT use_josm FROM user_preferences WHERE osm_id=:the_id";
        $stmt = $common->pdo->prepare($query);
        $stmt->bindParam(':the_id', $the_id);
        $stmt->execute();
        $_SESSION["use_josm"] = $stmt->fetch(PDO::FETCH_ASSOC)["use_josm"];
    }
        
	return true;
?>
