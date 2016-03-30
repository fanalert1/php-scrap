<?php

/*
*This PHP script will scrape the MovieCrow website for movie database.
*/

require_once('../vendor/autoload.php');
require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');

date_default_timezone_set("Asia/Calcutta");
$current_ts=date("Y/m/d H:i:s");
echo "Job Started on ".$current_ts."\n";

use Browser\Casper;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

$client = new Client();
$crawler = $client->request('GET', 'http://www.moviecrow.com/tamil/new-movies');

$movies_list=array();
$movie_key="";

$crawler->filter('#rank-tab-10')->each(function (Crawler $node, $i) {
             
            $node->filter('div.releasing_in')->each(function (Crawler $node, $i) {
                 
                        $node->filter('span.movieTitle')->each(function ($node) {
                             global $movie_key;
                             $movie_key=$node->text();
                             //echo $movie_key."\n";
                        });
                        $node->filter('span.movieLink')->each(function ($node) {
                             global $movies_list;
                             global $movie_key;
                             $movies_list[$movie_key]["link"]=$node->text();
                        });
                        $node->filter('span.movieDirector')->each(function ($node) {
                             global $movies_list;
                             global $movie_key;
                             $movies_list[$movie_key]["director"]=$node->text();
                        });
                        $node->filter('span.movieCast')->each(function ($node) {
                             global $movies_list;
                             global $movie_key;
                             $movies_list[$movie_key]["cast"]=$node->text();
                        });
                        $node->filter('span.movieMusicDirector')->each(function ($node) {
                             global $movies_list;
                             global $movie_key;
                             $movies_list[$movie_key]["music"]=$node->text();
                        });
                        $node->filter('span.movieImageLink')->each(function ($node) {
                             global $movies_list;
                             global $movie_key;
                             $movies_list[$movie_key]["poster"]=$node->text();
                        });
                        $node->filter('a.btn-play-t')->each(function ($node) {
                             global $movies_list;
                             global $movie_key;
                             global $client;
                             $link = $node->attr('href');
                             $crawler = $client->request('GET', $link);
                             $crawler->filter('iframe')->each(function ($node) {
                                global $movies_list;
                                global $movie_key;
                                $movies_list[$movie_key]["trailer"]=$node->attr('src');
                             });
                        });
                         
             
                         
             });
});

$current_ts=date("Y/m/d H:i:s");
foreach($movies_list as $key=>$values)
{
    
    $movie_name=$key; //sets movie name as key of the array
    $lang="Tamil";
    $present=isPresent($movie_name,$movies_collection,$lang);
    if($present=="")
    {
        $movie_details=get_imdb_det($movie_name);
        if(is_array($movie_details)&&(count($movie_details)==1))
        {
            foreach($movie_details as $details)
            {
                insertMovie($movies_collection,$movie_name,$lang);
                $movie_id=isPresent($movie_name,$movies_collection,$lang);
                $params=array("type" => "new", "prev_type"=>"null","det_stat"=>"new","poster_url"=>(gettype($details["poster"])=="string" ? $details["poster"] : "null"),"actors"=>is_null($details["cast"]) ? "null" : $details["cast"],"director"=>is_null($details["director"]) ? "null" : $details["director"],"music_director"=>is_null($details["music"]) ? "null" : $details["music"],"genre"=>is_null($details["genre"]) ? "null" : $details["genre"],"producer"=>is_null($details["producer"]) ? "null" : $details["producer"],"release_ts"=>is_null($details["release"]) ? "null" : $details["release"], "det_stat"=>"new","disabled"=>"false", "insert_ts" => $current_ts );
                updateMovieDetails($movies_collection,$movie_id,$params);
	           	updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"moviecrow");
	        		/*$result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$addToSet' => array("det_source"=> array("moviecrow" => array("title"=>$movie_name,"link"=>$values["link"])))],
	        		['upsert' => false]
	        		);*/
            }
        }
        else 
        {
            insertMovie($movies_collection,$movie_name,$lang);
            $movie_id=isPresent($movie_name,$movies_collection,$lang);
            $params = array("type" => "new", "prev_type"=>"null","det_stat"=>"new","disabled"=>"false", "insert_ts" => $current_ts );
	        updateMovieDetails($movies_collection,$movie_id,$params);
	        updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"moviecrow");
        }
    }else {
            $movie_id=$present;
            
            if(!checkSourceLink($movie,$values["link"]))
            updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"moviecrow");
        }
    
}

$current_ts=date("Y/m/d H:i:s");
echo "Job completed on ".$current_ts."\n";
?>
