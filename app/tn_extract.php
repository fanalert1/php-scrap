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


$tktnew_url='http://www.ticketnew.com/Movie-Ticket-Online-booking/C/Chennai';
$running_movies_links = array();
$running_movies_list = array();
$key="";

if(linkcheck($tktnew_url))
{
    $client = new Client();
    $crawler = $client->request('GET', 'http://www.ticketnew.com/Movie-Ticket-Online-booking/C/Chennai');
    $crawler->filter('div[id$="overlay-tab-booking-open"]')->each(function (Crawler $node, $i) {
        $node->filter('div[class$="titled-cornered-block"]')->each(function (Crawler $node, $i) {
            $node->filter('h3,li')->each(function ($node) {
                $content = $node->text();
                $item = trim($content);
                global $key;
                global $running_movies_list;
                if ($item=="Tamil")
                {
                   $key="Tamil";
                }
                else if ($item=="Tamil 3D")
                {
                   $key="Tamil";
                }
                else if($item=="English")
                {
                    $key="English";
                }
                else if($item=="English 2D")
                {
                    $key="English";
                }
                else if($item=="English 3D")
                {
                    $key="English";
                }
                else if($item=="Hindi")
                {
                    $key="Hindi";
                }
                else if($item=="Telugu")
                {
                    $key="Telugu";
                }
                else if($item=="Malayalam")
                {
                    $key="Malayalam";
                }
                else
                {
                    if(!in_array($item, $running_movies_list[$key])) //fix for child div issue for eng 2d 3d section
                        {
                            $running_movies_list[$key][] = $item;
                            $node->filter('a')->each(function (Crawler $node){ //fix for link order issue - tamil and english links clubbed for tamil dubbed english movie
                                global $running_movies_links;
                                $link = $node->link();
                                $uri = $link->getUri();
                                global $key;
                                $running_movies_links[$key][] = $uri;
                            });
                        }
                }
            });
        });
    });
    foreach($running_movies_list as $key=>$values)
    {
        
        print_r($values);
        $lang=$key; //sets language as key of the array
        foreach ($values as $key => $value)
        {
            $movie_name=$value; // sets movie name
           //$temp_name=str_replace(" ","-",$movie_name); //temporary variable to get the link of the movie from the array
            $movie_link=$running_movies_links[$lang][$key];
           // $present=isPresent($movie_name,$movies_collection);
            $prevs_type="null";
            $present=isPresent($movie_name,$movies_collection,$lang);
            
            if($present!="")
            {
                $event_movie_name= $movie_name; //for ticket new alone to include 2d,3d, dolby in event/notificaiton title
                $movie_name=title_clean($movie_name); //specially made for ticket new to remove 2d,3d, dolby, with english subtitle from movie title
               // $event_movie_name= $movie_name;
                $movie_id=$present;
                $movie=getDetail($movie_id,$movies_collection,"_id","type");
               	$current_type=$movie["type"];
                $prevs_type=$movie["prev_type"];
                
                echo "\n".$movie_name.$movie_id.$current_type.$prevs_type;
                
	        	//$upcoming=isUpcoming($movie_name,$movies_collection);
	        	$type="running";
	        	if($current_type=="upcoming")
	        	{
	        	    $prevs_type="upcoming";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	    
	        	//	if(!checkLink($movie,$movie_link))
	        	//	{
	        		updateMovieBookingLinks($movies_collection,$movie_id,$event_movie_name,$movie_link,"tktnew",$current_ts);
	        		
	        		$events = $events_collection->insertOne(
	        		array("movie_id"=>$movie_id,"movie_name"=>$event_movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "UR","opened_at" => "tktnew","notify"=> 'true',"insert_ts" => $current_ts ));
	        //		}
	        	}
	        	
	        	elseif($current_type=="running")
	        	{
	        	    
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	
	        		if(!checkLink($movie,$movie_link))
	        		{
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$event_movie_name,$movie_link,"tktnew",$current_ts);
	        	   // *********** Should event notification come here. separate notificaiton should be sent out when booking opens in ticketnew and bms //
	        	    $events = $events_collection->insertOne(
	        		array("movie_id"=>$movie_id,"movie_name"=>$event_movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "RR","opened_at" => "tktnew","notify"=> 'true',"insert_ts" => $current_ts ));
	        		}
	        	}
	        	
	        	elseif($current_type=="closed")
	        	{
	            	$prevs_type="closed";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        		
	        		if(!checkLink($movie,$movie_link))
	        		{
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$event_movie_name,$movie_link,"tktnew",$current_ts);
	        	    $events = $events_collection->insertOne(
	        		array("movie_id"=>$movie_id,"movie_name"=>$event_movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "RR","opened_at" => "tktnew","notify"=> 'true',"insert_ts" => $current_ts ));
	        		}
	        	}        
            }
            else {
                
                //$movie_details=get_imdb_det($movie_name);
                $event_movie_name= $movie_name; //for ticket new alone to include 2d,3d, dolby in event/notificaiton title
                $movie_name=title_clean($movie_name); //specially made for ticket new to remove 2d,3d, dolby, with english subtitle from movie title
                $movie_details=getMovieDetails($movie_name,$movie_link,$lang,"tktnew");
                
                //echo "\n";
                //echo $movie_name;
                //print_r($movie_details);
                //echo "\n";
                if(is_array($movie_details)&&(count($movie_details)==1))
                {
                    foreach($movie_details as $details)
                    {
                        insertMovie($movies_collection,$movie_name,$lang);
	        	    	$movie_id=isPresent($movie_name,$movies_collection,$lang);
	        	    	$params = array("type" => "running", "prev_type"=>"null","det_stat"=>"new","poster_url"=>$details["poster"],"actors"=>$details["cast"],"director"=>$details["director"],"music_director"=>$details["music"],"genre"=>$details["genre"],"producer"=>$details["producer"],"release_ts"=>$details["release"], "disabled"=>"false", "insert_ts" => $current_ts );  
	        	    	updateMovieDetails($movies_collection,$movie_id,$params);
	        	    	updateMovieBookingLinks($movies_collection,$movie_id,$event_movie_name,$movie_link,"tktnew",$current_ts);
	                    
	        	    	$events = $events_collection->insertOne(
	        	    	array("movie_id"=>$movie_id,"movie_name"=>$event_movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FR","opened_at" => "tktnew","notify"=> 'true',"insert_ts" => $current_ts ));
	                    
                    }
                }
                else 
                {
                        insertMovie($movies_collection,$movie_name,$lang);
    	        	    $movie_id=isPresent($movie_name,$movies_collection,$lang);
    	        	    $params = array("type" => "running", "prev_type"=>"null","det_stat"=>"new","disabled"=>"false","insert_ts" => $current_ts);  
    	        	    updateMovieDetails($movies_collection,$movie_id,$params);
    	                updateMovieBookingLinks($movies_collection,$movie_id,$event_movie_name,$movie_link,"tktnew",$current_ts);
    	                
    	        	    $events = $events_collection->insertOne(
    	        	    array("movie_id"=>$movie_id,"movie_name"=>$event_movie_name, "lang"=>$lang, "event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FR","opened_at" => "tktnew","notify"=> 'true',"insert_ts" => $current_ts ));
                }
            }
        }
    }
}
$current_ts=date("Y/m/d H:i:s");
echo "Job completed on ".$current_ts."\n";
?>