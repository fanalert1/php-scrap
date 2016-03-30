<?php

/*
*This PHP script will scrape the Filmibeat website for movie database.
*/

require_once('../vendor/autoload.php');
require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');


date_default_timezone_set("Asia/Calcutta");
$current_ts=date("Y/m/d H:i:s");
echo "Filmibeat extract job Started on ".$current_ts."\n";

use Browser\Casper;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

$current_month = date("M");
$date = new DateTime(date("Y/m/d"));
$date->modify('first day of +1 month');
$next_month =  $date->format('M');

$month=array($current_month,$next_month);

$client = new Client();
$crawler = $client->request('GET', 'http://www.filmibeat.com/tamil/upcoming-movies.html');

$movies_list=array();
$movie_key="";

foreach($month as $mon)
{
    $crawler->filter('#'.$mon)->each(function (Crawler $node, $i) {
        $node->filter('li')->each(function (Crawler $node, $i) {
            $node->filter('h3.filmibeat-db-upcoming-movie-title > a')->each(function ($node) {
                global $movie_key;
                global $movies_list;
                $movie_key=trim($node->text());
                $movies_list[$movie_key]["link"]= $node->attr('href');
            });
            $node->filter('div.filmibeat-db-nextchange-reldate')->each(function ($node) {
                global $movies_list;
                global $movie_key;
                $release=explode("-",$node->text());
                //echo $movie_key."  ".trim($release[1])."\n";
                $movies_list[$movie_key]["release"]=date("Y/m/d H:i:s",strtotime($release[1]));
            });
        });
    });

}

$current_ts=date("Y/m/d H:i:s");
foreach($movies_list as $key=>$values)
{
    $movie_name=$key; //sets language as key of the array
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
	           	updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"filmibeat");
	        	
	           	/*if(!checkLink($movie,$movie_link))
	        		$result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$addToSet' => array("det_source"=> array("filmibeat" => array("title"=>$movie_name,"link"=>$values["link"])))],
	        		['upsert' => false]
	        		);
	           	*/
            }
        }
        else 
        {
            insertMovie($movies_collection,$movie_name,$lang);
            $movie_id=isPresent($movie_name,$movies_collection,$lang);
            $params = array("type" => "new", "prev_type"=>"null","det_stat"=>"new","disabled"=>"false", "insert_ts" => $current_ts );
	        updateMovieDetails($movies_collection,$movie_id,$params);
	        updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"filmibeat");
        }
    }else{
        
        $movie_id=$present;
            
        if(!checkSourceLink($movie,$values["link"]))
        updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"filmibeat");
    }
}

$current_ts=date("Y/m/d H:i:s");
echo "Filmibeat extract job completed on ".$current_ts."\n";
?>