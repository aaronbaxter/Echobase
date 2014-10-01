<?php
//gets xy and returns tileinfo

$lat = $_GET['lat'];
$lon = $_GET['lon'];

include 'echobase_classes.php';

$echobase = new echobase();

$tileinfo = $echobase->xy2tile($lon,$lat);

echo json_encode($tileinfo);

?>