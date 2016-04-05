<?php

/*
*This PHP script will scrape the wikipedia website for movie database.
*/

require_once('../vendor/autoload.php');
require_once('imdb_get.php');
require_once('config/db1.php');
require('global_function.php');

date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Wiki extract job Started on ".$current_ts."\n";

//require_once(__DIR__ . '/vendor/autoload.php');
//require_once(__DIR__ . '/src/Browser/Casper.php');
date_default_timezone_set("Asia/Calcutta");

use Browser\Casper;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

$client = new Client();
$crawler = $client->request('GET', 'https://en.wikipedia.org/wiki/List_of_Tamil_films_of_2016');
$movies_list=array();
$movie="";
$field="";
$info="";
$info1=array();
$crawler->filter('table[class$="wikitable sortable"]')->each(function (Crawler $node, $i) {
    $node->filter('i > a')->each(function (Crawler $node, $i) {
        global $client;
        global $movie;
        global $movies_list;
        $movie=$node->text();
        $link = "https://en.wikipedia.org".$node->attr('href');
        $movies_list[$movie]["link"]=$link;
        $crawler = $client->request('GET', $link);
        $crawler->filter('table.infobox.vevent')->each(function (Crawler $node, $i) 
        {
            $node->filter('tr')->each(function (Crawler $node, $i) 
            {
                global $movies_list;
                global $movie;
                global $field;
                global $info;
                global $info1;
                $node->filter('th')->each(function ($node) {
                                 global $field;
                                 $field=trim($node->text());
                            });
                $node->filter('td')->each(function ($node) 
                {
                     global $info;
                     global $field;
                     global $info1;
                     $filter=$node->filter('a');
                     if (iterator_count($filter) > 1) 
                     {
                        // iterate over filter results
                        foreach ($filter as $i => $content) {
                        // create crawler instance for result
                        $crawler = new Crawler($content);
                        // extract the values needed
                        $info1[$i] = $crawler->filter('a')->text();
                        $info="";
            
                    }
                    } else {
                        $info=trim($node->text());
                    }
                    
                });
                $node->filter('img')->each(function ($node) {
                     global $info;
                     global $field;
                     $field="poster";
                     $info=trim($node->attr('src'));
                });
                
                if ($field=="Directed by") {
                    $movies_list[$movie]["director"]= empty($info) ? $info1 : $info;
                }elseif ($field=="Produced by") {
                    $movies_list[$movie]["producer"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Written by") {
                    $movies_list[$movie]["writer"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Starring") {
                    $movies_list[$movie]["cast"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Music by") {
                    $movies_list[$movie]["music"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Release dates") {
                    $movies_list[$movie]["release"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Language") {
                    $movies_list[$movie]["lang"]=empty($info) ? $info1 : $info;
                }elseif ($field=="poster") {
                    $movies_list[$movie]["poster"]=empty($info) ? $info1 : $info;
                }
            });
            
        });
        $crawler->filter('p:nth-child(2)')->each(function (Crawler $node, $i) {
            global $movies_list;
            global $movie;
            $movies_list[$movie]["synopsis"]=$node->text();
            
        });
        
    });
    
});

$current_ts=date("Y/m/d H:i:s");
$type = "new";

foreach($movies_list as $key=>$values)
    {
        $movie_name=$key;
        
        $present=isPresent($movie_name,$movies_collection,"Tamil");
        if($present=="")
        {
            insertMovie($movies_collection,$movie_name,"Tamil");
            $movie_id=isPresent($movie_name,$movies_collection,"Tamil");
            $params =array("type" => $type,"poster_url"=>is_null($values["poster"]) ? "null" : $values["poster"], "actors"=>is_null($values["cast"]) ? "null" : $values["cast"],"director"=>is_null($values["director"]) ? "null" : $values["director"],"music_director"=>is_null($values["music"]) ? "null" : $values["music"],"genre"=>"null","producer"=>is_null($values["producer"]) ? "null" : $values["producer"],"release_ts"=>is_null($values["release"]) ? "null" : date("Y/m/d",strtotime($values["release"])),"synopsis"=>is_null($values["synopsis"]) ? "null" : $values["synopsis"],"det_stat"=>"new","disabled"=>"true","insert_ts" => $current_ts );
            updateMovieDetails($movies_collection,$movie_id,$params);
            updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"wiki");
            
            /*if(!checkLink($movie,$values["link"]))
	        		$result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$addToSet' => array("det_source"=> array("wiki" => array("title"=>$movie_name,"link"=>$values["link"])))],
	        		['upsert' => false]
	        		);*/
        }else 
        {
            $movie_id=$present;
            
            if(!checkSourceLink($movie,$values["link"]))
            updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$values["link"],"wiki");
        }
    }
$current_ts=date("Y/m/d H:i:s");
echo "Wiki extract job completed on ".$current_ts."\n";

?>