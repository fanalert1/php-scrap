<?php

//require_once('../../vendor/autoload.php');


//DB connection
$client = new MongoDB\Client("mongodb://128.199.141.102:27017");
$movies_collection = $client->fanalert->movies;
$events_collection = $client->fanalert->events;
//$tokens_collection = $client->firedb->device_tokens;
$counter_collection = $client->fanalert->counter;




?>