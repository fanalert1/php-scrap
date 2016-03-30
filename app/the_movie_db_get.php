<?php


//get wikipedia title and then search.
$url=""
$handle = curl_init($url);
curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
$response = curl_exec($handle);
http://api.themoviedb.org/3/search/movie?query=theri&year=2016&api_key=49828bb0978e2715a4497ecb369c3697



?>