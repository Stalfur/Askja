<?php
	include ('includes/connect.php');
	include ("includes/language.php");
	include ("includes/class.osmuser.php");
	$osmUser = new osmUser();
	$osmUser->setPDO($dbh);
	
	$pagetitle = _preferences;
	include ('includes/header.php');
	
	$osmUser->checkPreferences($_SESSION["osm_id"]);
?>

<form>
<?php
echo $osmUser->getPreferences($_SESSION["osm_id"]);
?>
</form>

<script>
	function updPrefences(use_josm)
	{		
		$.ajax({
			url : 'ajax/ajax-preferences.php',
			data : 'use_josm='+use_josm,
			type : 'POST',
			success : function(data,status) {
				location.reload(true);
			},
			error : function() {
				alert('<?php echo _errorupdating; ?>');
			}
		});
	}
</script>
<?php include("includes/footer.php");