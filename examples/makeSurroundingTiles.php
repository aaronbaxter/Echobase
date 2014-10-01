<?php
//for use in tilegenerator gui
//creates tiles around a particular lat/lng
ini_set("memory_limit","1024M");
$x = $_GET["lon"];
$y = $_GET["lat"];
$n = $_GET["n"]; //number of tiles to make in each direction

include 'echobase_classes.php';

$echobase = new echobase();
$terrainParser = new terrainParser();
$osmParser = new osmParser();

//loop through the x
for($i=0;$i<$n;$i++){
	$lon = $x - ($n/2)*0.02 + $i*0.02;
	//loop through the y
	// print "lon: $lon <br>";
	for($j=0;$j<$n;$j++){
		$lat = $y - ($n/2)*0.02 + $j*0.02;
		// print "lat: $lat <br>";
		$tileinfo = $echobase->xy2tile($lon,$lat);
		// print_r($tileinfo);
		// $terrainParser->writeAscTile($tileinfo);
		// $terrainParser->writeDummyAscTile($tileinfo);
		// $osmParser->createOsmTile($tileinfo);
		echo "<a href=\"http://localhost/echoscapes/tilegenerator/writeTile.php?tilename=".$tileinfo["tilename"]."&tiletype=asc\">".$tileinfo["tilename"].".asc</a><br>";
		echo "<a href=\"http://localhost/echoscapes/tilegenerator/writeTile.php?tilename=".$tileinfo["tilename"]."&tiletype=o5m\">".$tileinfo["tilename"].".o5m</a><br>";

	}

}
?>