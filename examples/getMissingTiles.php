<?php
//for use in tilegenerator gui
//returns list of missing tiles

include 'echobase_classes.php';

$echobase = new echobase();

$missingTiles = $echobase->checkCoverage();

echo json_encode($missingTiles);

?>
