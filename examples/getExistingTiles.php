<?php
//get existing tiles

include 'echobase_classes.php';

$echobase = new echobase();

$existingTiles = $echobase->getExistingTileList();

echo json_encode($existingTiles);

?>