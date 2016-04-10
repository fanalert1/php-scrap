<?php

require_once('../vendor/autoload.php');
require_once('config/db.php');
require('global_function.php');

date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Theatre check BMS Job Started on ".$current_ts."\n";

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
$theatres=array();
$client = new Client();
$crawler = $client->request('GET', "https://in.bookmyshow.com/buytickets/theri-chennai/movie-chen-ET00036069-MT/20160414");

$crawler->filter('div.details > div.__name > a > strong')->each(function (Crawler $node, $i) {
   global $theatres;
   $theatres[]= $node->text();
   
   
    
});
print_r($theatres);
foreach ($theatres as $theatre)
{
    
    createTheaterEvent($events_collection,$counter_collection,"570392b6f7478646a55a1a4f","Theri","Tamil",$theatre,"bms");
    
}
    /*$present = $events_collection->findOne(array(["movie_id"=>new MongoDB\BSON\ObjectId('570392b6f7478646a55a1a4f'),"theatre"=>$theatre]));
       print_r($present);
  //  findOne(['_id' => $id]);
  if(!isset($present))
    {
        
    	$events = $events_collection->insertOne(
	        		array("movie_id"=>new MongoDB\BSON\ObjectId('570392b6f7478646a55a1a4f'),"movie_name"=>"Theri","lang"=>"Tamil","theatre"=>$theatre,"opened_at" => "bms",
	        		"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "TH","notify"=> 'false',"insert_ts" => $current_ts ));
  
    }
    else{
        
        echo "theatre event exists";
    }*/


echo "Theatre check BMS Job completed on ".date("Y/m/d H:i:s")."\n";
?>