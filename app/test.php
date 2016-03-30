<?php

require_once('../vendor/autoload.php');
//require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');
//require_once('scrap_details.php');
use Bing\Client;
     
     $movie_name = "Ki And Ka";
     $search_type = "Movie";
     
     $lang = "Tamil";
     
     
    $movie_details=array();
   $wiki_url = bingSearch($movie_name,"wiki");
   $imdb_url =  bingSearch($movie_name,"imdb");
   $fb_url =  bingSearch($movie_name,"filmibeat");
   echo $wiki_url;
   //  echo $result."\n";
   if($wiki_url!="")
   {
       $movie_details[0]=wiki_scrap($movie_name,$wiki_url);
     //  $movie_details[0]
      // if($callFrom=="tktnew")
      // {
       //$movie_details[0]["release"]=date("Y/m/d H:i:s",strtotime(tktnew_scrap($movie_name,$movie_link)));
       //$movie_details[0]["release"]=tktnew_scrap($movie_name,$movie_link);
    //  }
       //echo "\nFound in Wiki"."\n";
      print_r($movie_details);
      // return $movie_details; 
   }
     
     
     
     //{"responseData": null, "responseDetails": "Suspected Terms of Service Abuse. Please see http://code.google.com/apis/errors", "responseStatus": 403}
     /*
     
     $links=googleApiSearch($movie_name,$search_type);
     
     foreach($links as $key=>$link)
     
     {
      //    echo $key;
      
      if($key=="wiki")
      {
          echo $link;
          $url = $link;
      }
          //$url = $link["wiki"];
          
     }
     
     echo "test";
     
     $details = wiki_scrap($movie_name,$url);
     
     print_r($details);
     
     
 
     
     
         Array
     (
    [wiki] => https://en.wikipedia.org/wiki/Oopiri
    [imdb] => http://www.imdb.com/title/tt5039054/
    [themoviedb] => http://www.imdb.com/year/2016/
    [filmibeat] => http://www.filmibeat.com/tamil/reviews/2016/thozha-movie-review-rating-plot-story-celebrates-life-karthi-nagarjuna-220426.html
     )
     
     
     
     $present=isPresent($value,$movies_collection,$lang);
     if ($present!="")
     {
     echo $present;
   //  $realmongoid = new MongoId($mongoid);
     $movie = $movies_collection->findOne(['_id' => $present]);
     //print_r( $movie);
    // print_r($movie["source"][1]["tktnew"]);
     
     
     
          foreach($movie["source"] as $source) 
          {
               foreach($source as $key=>$value)
               
               {
                    //echo $key;
                    //print_r($value);
                    echo $value["link"];
                    
               }
               //print_r($source["tktnew"]["link"]);
          }
     }
     else
     echo "not found";
     
     */
?>