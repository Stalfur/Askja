<table border="0" align="right"><tr><td>
<?php
if ($lang == "ISL")
	{ echo "Íslenska"; }
else
	{ echo '<a href="setcookie.php?ling=ISL">Íslenska</a>'; }
/*
if ($lang == "ESP")
	{ echo " | Español"; }
else
	{ echo ' | <a href="setcookie.php?ling=ESP">Español</a>'; }
if ($lang == "ITA")
	{ echo " | Italiano"; }
else
	{ echo ' | <a href="setcookie.php?ling=ITA">Italiano</a>'; }
if ($lang == "FRA")
	{ echo " | Français"; }
else
	{ echo ' | <a href="setcookie.php?ling=FRA">Français</a>'; }
*/
if ($lang == "ENG")
	{ echo " | English"; }
else
	{ echo ' | <a href="setcookie.php?ling=ENG">English</a>'; }
?>
</td></tr></table>