<?php

require_once('../vendor/autoload.php');
//require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');
//require_once('scrap_details.php');
use Bing\Client;
     
     $movie_name = "Narathan";
     $movie_link = "http://www.ticketnew.com/Zero-Movie-Tickets-Online-Show-Timings/Online-Advance-Booking/10378/C/Chennai";
     $search_type = "Movie";
     
     $lang = "Tamil";
     
     
     /*
     $movie_name2 = "batman&supermandawnofjustice";
     similar_text($movie_name, $movie_name2, $p); 
     echo "Percent: $p"; 
   */
  // $movie_details=get_imdb_det($movie_name);
   
  $movie_details=array();
  $movie_details=getMovieDetails($movie_name,$movie_link,$lang,"tktnew");
   
   print_r($movie_details);
   
   
   
   
   
   
 //  $wiki_url = bingSearch($movie_name,$lang,"wiki");
 //  $imdb_url =  bingSearch($movie_name,$lang,"imdb");
 //  $fb_url =  bingSearch($movie_name,$lang,"filmibeat");
   // echo $wiki_url;
   //  echo $result."\n";
   
   /*
   if($wiki_url!="")
   {
       $movie_details[0]=wiki_scrap($movie_name,$wiki_url);
   
      print_r($movie_details);
   
   }
     */
     
     
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