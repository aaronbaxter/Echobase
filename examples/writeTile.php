<?php
//for use in tilegenerator gui
//writes a single tile given a tilename and tiletype
//

$tiletype = $_GET['tiletype'];
$tilename = $_GET['tilename'];


include 'echobase_classes.php';

$echobase = new echobase();
$terrainParser = new terrainParser();
$osmParser = new osmParser();


$bounds =  $echobase->tilename2bounds($tilename);
$tileinfo = $echobase->xy2tile($bounds["west"]+0.01,$bounds["south"]+0.01);
if($tiletype == "asc") {
	$terrainParser->writeAscTile($tileinfo);	
	echo "wrote $tilename";
}
elseif($tiletype == "o5m"){
	$osmParser->createOsmTile($tileinfo);
	echo "wrote $tilename";
}
else {
	echo "did not write tile";
}


?>