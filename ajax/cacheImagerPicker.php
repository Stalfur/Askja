<?php
	include ('../includes/connect.php');
	include ('../includes/class.settlements.php');
	
	$settlements = new settlements();
	
	$returner = $settlements->updateCountriesMissingImagery();