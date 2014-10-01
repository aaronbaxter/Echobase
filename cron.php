<?php
//script that checks whether there are any new echoscape tiles to make

include 'echobase_classes.php';

$echobase = new echobase();

$missingTiles = $echobase->checkCoverage();

//loop through needed osm
foreach ($missingTiles["asc"] as $tile => $value) {
	$bounds =  $echobase->tilename2bounds($value);
	$tileinfo = $echobase->xy2tile($bounds["west"]+0.01,$bounds["south"]+0.01);
}


//loop through needed srtm
foreach ($missingTiles["o5m"] as $tile => $value) {
	$bounds =  $echobase->tilename2bounds($value);
	$tileinfo = $echobase->xy2tile($bounds["west"]+0.01,$bounds["south"]+0.01);
	$osmParser->createOsmTile($tileinfo);
}



?>