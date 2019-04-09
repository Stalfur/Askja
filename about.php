<?php
	include ('includes/connect.php');
	include ("includes/language.php");
	
	$pagetitle = _about;
	include ('includes/header.php');
?>
<p>
This website is for OpenStreetMap users to rank the quality, and to improve the quality, of various places.
</p>

<p>
Each aspect has its own icon, and each icon has a (color-blind friendly) color scheme to show its quality. <br />
By default everything is ranked as unknown (grey color). <br />
After logging in users can assign the appropriate color.
</p>
<h2>Settlements</h2>
<table border="1">
    <title>Settlements</title>
    <tr>
        <th>Marker type</th>
        <th>Good coverage</th>
        <th>Partial coverage</th>
        <th>No coverage</th>
        <th>Unknown</th>
    </tr>
    <tr>
        <td align="center"><img src="img/black/highway.png"><br/><b>Highway system</b></td>
        <td align="center"><img src="img/blu-green/highway.png"><br/>Connected</td>
        <td align="center"><img src="img/orange/highway.png"><br/>No road but ferry/airport</td>
        <td align="center"><img src="img/vermillion/highway.png"><br/>Not connected</td>
        <td align="center"><img src="img/grey/highway.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/streets.png"><br/><b>Internal roads</b></td>
        <td align="center"><img src="img/blu-green/streets.png"><br/>All visible mapped</td>
        <td align="center"><img src="img/orange/streets.png"><br/>Some mapped</td>
        <td align="center"><img src="img/vermillion/streets.png"><br/>None mapped</td>
        <td align="center"><img src="img/grey/streets.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/buildings.png"><br/><b>Buildings</b></td>
        <td align="center"><img src="img/blu-green/buildings.png"><br/>All visible mapped</td>
        <td align="center"><img src="img/orange/buildings.png"><br/>Some mapped</td>
        <td align="center"><img src="img/vermillion/buildings.png"><br/>None mapped</td>
        <td align="center"><img src="img/grey/buildings.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/cloudysunny.png"><br/><b>Imagery</b></td>
        <td align="center"><img src="img/blu-green/sunny.png"><br/>Good imagery</td>
        <td align="center"><img src="img/orange/cloudysunny.png"><br/>Partial imagery</td>
        <td align="center"><img src="img/vermillion/cloudy.png"><br/>Unusable imagery</td>
        <td align="center"><img src="img/grey/cloudysunny.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/addresses.png"><br/><b>Address coverage</b></td>
        <td align="center"><img src="img/blu-green/addresses.png"><br/>Excellent</td>
        <td align="center"><img src="img/orange/addresses.png"><br/>Partial</td>
        <td align="center"><img src="img/vermillion/addresses.png"><br/>None</td>
        <td align="center"><img src="img/grey/addresses.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/amenities.png"><br/><b>Amenities (social services)</b></td>
        <td align="center"><img src="img/blu-green/amenities.png"><br/>Most mapped</td>
        <td align="center"><img src="img/orange/amenities.png"><br/>Some mapped</td>
        <td align="center"><img src="img/vermillion/amenities.png"><br/>None mapped</td>
        <td align="center"><img src="img/grey/amenities.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/paths.png"><br/><b>Paths/tracks</b></td>
        <td align="center"><img src="img/blu-green/paths.png"><br/>Most mapped</td>
        <td align="center"><img src="img/orange/paths.png"><br/>Some mapped</td>
        <td align="center"><img src="img/vermillion/paths.png"><br/>None mapped</td>
        <td align="center"><img src="img/grey/paths.png"><br/>Not surveyed</td>
    </tr>
    <tr>
        <td align="center"><img src="img/black/photo.png"><br/><b>Mapillary images</b></td>
        <td align="center"><img src="img/blu-green/photo.png"><br/>Excellent coverage</td>
        <td align="center"><img src="img/orange/photo.png"><br/>Some coverage</td>
        <td align="center"><img src="img/vermillion/photo.png"><br/>No coverage</td>
        <td align="center"><img src="img/grey/photo.png"><br/>Not surveyed</td>
    </tr>
</table>

<br clear="all"/>
<p>Icons created using <a hreF="https://mapicons.mapsmarker.com">Map Icons Collection</a>.</p>
</div>
<?php include("includes/footer.php");?>