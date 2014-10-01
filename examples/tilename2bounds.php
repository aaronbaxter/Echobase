<?php

$tilename = $_GET['tilename'];

include 'echobase_classes.php';

$echobase = new echobase();

$bounds = $echobase->tilename2bounds($tilename);

echo json_encode($bounds);

?>