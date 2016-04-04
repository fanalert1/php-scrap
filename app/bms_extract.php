<?php
require_once('../vendor/autoload.php');
require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');


date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Job Started on ".$current_ts."\n";

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;


$bms_url='https://in.bookmyshow.com/chennai/movies/nowshowing';

if(linkcheck($bms_url))
{
    $client = new Client();
	$crawler = $client->request('GET', $bms_url);
	$upcoming_movies=array();
	$i=0;
	$j=0;
	$crawler->filter('#now-showing > section.now-showing.filter-now-showing > div > div.__col-now-showing')->each(function (Crawler $node, $i) {
	    $node->filter('div.detail')->each(function (Crawler $node, $i) {
	        global $i,$j;
	           $node->filter('div.__name > a')->each(function ($node)
	               {
	                   global $i;
	                   global $upcoming_movies;
	                    $upcoming_movies[$i]["name"] = $node->text();
	                    
	                    $link = $node->attr('href');
	                    $upcoming_movies[$i]["link"] = "https://in.bookmyshow.com".$link;
	                });
	            $node->filter('div.languages > ul > li')->each(function ($node)
	               {
	                   global $i,$j;
	                    global $upcoming_movies;
	                    $upcoming_movies[$i]["lang"][$j] = $node->text();
	                    $j+=1;
	                });
	        $i +=1;
	        $j=0;
	    });
    
	});
	
	
foreach($upcoming_movies as $movies)
{
    $movie_name = $movies["name"];
    $movie_link = $movies["link"];
    print_r($movies["lang"]);
    
    foreach ($movies["lang"] as $lang) 
    
    {
   			//echo "$value <br>";

    		$lang=trim($lang);
    		$lang=str_replace(',','',$lang); //fix for comma coming along with lang issue
    		
    		$prevs_type="null";
	        $present=isPresent($movie_name,$movies_collection,$lang);
	        
		    if($present!="")
            {
                $movie_id=$present;
		        $movie=getDetail($movie_id,$movies_collection,"_id","type");
               	$current_type=$movie["type"];
                $prevs_type=$movie["prev_type"];
				$type="running";
				if($current_type=="upcoming")
				{
					
					$prevs_type="upcoming";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	    
	        		if(!checkLink($movie,$movie_link))
	        		{
	        		updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
			        		    
					$events = $events_collection->insertOne(
					array("movie_id"=>$movie_id,"movie_name"=>$movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "UR","opened_at" => "bms","notify"=> 'true',"insert_ts" => $current_ts ));
	        		}
				}
				elseif($current_type=="running")
				{
				    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	
	        		if(!checkLink($movie,$movie_link))
	        		{
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
	        	    
	        	    $events = $events_collection->insertOne(
					array("movie_id"=>$movie_id,"movie_name"=>$movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "RR","opened_at" => "bms","notify"=> 'true',"insert_ts" => $current_ts ));
	        		
	        		}
				}
				elseif($current_type=="closed")
				{
			    	$prevs_type="closed";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        		
	        		if(!checkLink($movie,$movie_link))
	        		{
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
	        	    $events = $events_collection->insertOne(
					array("movie_id"=>$movie_id,"movie_name"=>$movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "RR","opened_at" => "bms","notify"=> 'true',"insert_ts" => $current_ts ));
	        		
	        		}
				}        
		    }
		    
		    else {
		        
		        //$movie_details=get_imdb_det($movies["name"]);
		       // $movie_details=getMovieDetails($movies["name"],"");
		        $movie_details=getMovieDetails($movie_name,$movie_link,$lang,"bms");
		        
		        
		        if(is_array($movie_details)&&(count($movie_details)==1))
		        {
		            foreach($movie_details as $details)
		            {
		                insertMovie($movies_collection,$movie_name,$lang);
	        	    	$movie_id=isPresent($movie_name,$movies_collection,$lang);
	        	    	$params = array("type" => "running", "prev_type"=>"null","det_stat"=>"new","poster_url"=>$details["poster"],"actors"=>$details["cast"],"director"=>$details["director"],"music_director"=>$details["music"],"genre"=>$details["genre"],"producer"=>$details["producer"],"release_ts"=>$details["release"], "disabled"=>"false", "insert_ts" => $current_ts );  
	        	    	updateMovieDetails($movies_collection,$movie_id,$params);
	        	    	updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
			            
				    	$events = $events_collection->insertOne(
				    	array("movie_id"=>$movie_id,"movie_name"=>$movie_name, "lang" => $lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FR","opened_at" => "bms","notify"=> 'true',"insert_ts" => $current_ts ));
			            
		            }
		        }
		        else 
		        {
		                insertMovie($movies_collection,$movie_name,$lang);
    	        	    $movie_id=isPresent($movie_name,$movies_collection,$lang);
    	        	    $params = array("type" => "running", "prev_type"=>"null","det_stat"=>"new","disabled"=>"false","insert_ts" => $current_ts);  
    	        	    updateMovieDetails($movies_collection,$movie_id,$params);
    	                updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
			            
						
						$events = $events_collection->insertOne(
				    	array("movie_id"=>$movie_id,"movie_name"=>$movie_name, "lang"=>$lang, "event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FR","opened_at" => "bms","notify"=> 'true',"insert_ts" => $current_ts ));
			            
		        }
		        
		    }
	    
    }
}
}
$current_ts=date("Y/m/d H:i:s");
echo "Job completed on ".$current_ts."\n";
?>