<?php

require_once(__DIR__ . '/vendor/autoload.php');//Autoload required API's

//DB connection
$client = new MongoDB\Client;
$collection = (new MongoDB\Client)->firedb->config;

$document = $collection->find();
var_dump($document);

?>