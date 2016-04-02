<?php 

require_once('../vendor/autoload.php');
require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');//Autoload required API's

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Upcoming Extract Job Started on ".$current_ts."\n";

$upcoming_movies_list=array();
$upcoming_movies_links=array();
$key="";
$i=0;
$j=0;
$cur_mon=date("m");
if($cur_mon=="12")
{
    $next_mon="01";
}
else
{
    $date = new DateTime(date("Y/m/d"));
    $date->modify('first day of +1 month');
    $next_mon =  $date->format('m');
}

function tktnew_upcoming()
{
    global $key;
    global $upcoming_movies_list;
    global $upcoming_movies_links, $movies_collection,$events_collection;
    
    $client = new Client();
    $crawler = $client->request('GET', 'http://www.ticketnew.com/Movie-Ticket-Online-booking/C/Chennai');
    
    $crawler->filter('div[id$="overlay-tab-coming-soon"]')->each(function (Crawler $node, $i) {
    
                 $node->filter('div[class$="titled-cornered-block"]')->each(function (Crawler $node, $i) {
    
                             $node->filter('h3,li')->each(function ($node) {
    
                                       $content = $node->text();
                    $item = trim($content);
                    //echo $item;
                    global $key;
                    global $upcoming_movies_list;
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
                        if(!in_array($item, $upcoming_movies_list[$key])) //fix for child div issue for eng 2d 3d section
                            {
                                $upcoming_movies_list[$key][] = $item;
                                $node->filter('a')->each(function (Crawler $node){ //fix for link order issue - tamil and english links clubbed for tamil dubbed english movie
                                    global $upcoming_movies_links;
                                    $link = $node->link();
                                    $uri = $link->getUri();
                                    global $key;
                                    $upcoming_movies_links[$key][] = $uri;
                                });
                            }
                    }
                });
            });
        });

foreach($upcoming_movies_list as $key=>$values)
    {
        
        print_r($values);
        $lang=$key; //sets language as key of the array
        foreach ($values as $key => $value)
        {
            $movie_name=$value; // sets movie name
           //$temp_name=str_replace(" ","-",$movie_name); //temporary variable to get the link of the movie from the array
            $movie_link=$upcoming_movies_links[$lang][$key];
           // $present=isPresent($movie_name,$movies_collection);
            $prevs_type="null";
            $present=isPresent($movie_name,$movies_collection,$lang);
            
            if($present!="")
            {
                $movie_id=$present;
                $movie=getDetail($movie_id,$movies_collection,"_id","type");
               	$current_type=$movie["type"];
                $prevs_type=$movie["prev_type"];
                
                echo "\n".$movie_name.$movie_id.$current_type.$prevs_type;
                
	        	//$upcoming=isUpcoming($movie_name,$movies_collection);
	        	$type="upcoming";
	        	if($current_type=="new")
	        	{
	        	    $prevs_type="upcoming";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	    
	        		if(!checkLink($movie,$movie_link))
	        		updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"tktnew",$current_ts);
	        		//$events = $events_collection->insertOne(
	        		//array("movie_name"=>$movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FU","notify"=> 'true',"insert_ts" => $current_ts ));
	        
	        	}elseif($current_type=="running")
				{
				    updateMovieType($movies_collection,$movie_id,$current_type,$prevs_type,$current_ts);
	        	
	        		if(!checkLink($movie,$movie_link))
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"tktnew",$current_ts);
				}
	        	
	        	elseif($current_type=="upcoming")
	        	{
	        	    
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	
	        		if(!checkLink($movie,$movie_link))
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"tktnew",$current_ts);
	        	   // *********** Should event notification come here. separate notificaiton should be sent out when booking opens in ticketnew and bms //
	        	  
	        	}
	        	
	        	elseif($current_type=="closed")
	        	{
	            	$prevs_type="closed";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        		
	        		if(!checkLink($movie,$movie_link))
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"tktnew",$current_ts);
	        	}        
            }
            else {
                
                //$movie_details=get_imdb_det($movie_name);
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
	        	    	$params = array("type" => "upcoming", "prev_type"=>"null","det_stat"=>"new","poster_url"=>$details["poster"],"actors"=>$details["cast"],"director"=>$details["director"],"music_director"=>$details["music"],"genre"=>$details["genre"],"producer"=>$details["producer"],"release_ts"=>$details["release"], "disabled"=>"false", "insert_ts" => $current_ts );  
	        	    	updateMovieDetails($movies_collection,$movie_id,$params);
	        	    	updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"tktnew",$current_ts);
	                    
	        	    //	$events = $events_collection->insertOne(
	        	    //	array("movie_name"=>$movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FR","notify"=> 'true',"insert_ts" => $current_ts ));
	                    
                    }
                }
                else 
                {
                        insertMovie($movies_collection,$movie_name,$lang);
    	        	    $movie_id=isPresent($movie_name,$movies_collection,$lang);
    	        	    $params = array("type" => "upcoming", "prev_type"=>"null","det_stat"=>"new","disabled"=>"false","insert_ts" => $current_ts);  
    	        	    updateMovieDetails($movies_collection,$movie_id,$params);
    	                updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"tktnew",$current_ts);
    	                
    	        	    //$events = $events_collection->insertOne(
    	        	    //array("movie_name"=>$movie_name, "lang"=>$lang, "event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FU","notify"=> 'true',"insert_ts" => $current_ts ));
                }
            }
        }
    }
    
    unset($GLOBALS['upcoming_movies_list']);
    unset($GLOBALS['upcoming_movies_links']);
    unset($GLOBALS['key']);
    $current_ts=date("Y/m/d H:i:s");
    echo "\nTicket New Upcoming Extract Job completed on ".$current_ts."\n";
}

function bms_upcoming()
{
     global $upcoming_movies_list;
     global $movies_collection,$events_collection;
    $client = new Client();
	$crawler = $client->request('GET', "https://in.bookmyshow.com/chennai/movies/comingsoon");
	$crawler->filter('#coming-soon > section._release-calendar-filter.filter-now-showing > div > div.__col-now-showing > div.release-calandar.mv-row > aside')->each(function (Crawler $node, $i) {
	    global $cur_mon , $next_mon , $key;
	    $month=$node->attr("data-month");
	    $key = $node->attr("");
	    if($month==$cur_mon || $month==$next_mon)
	    {
	        $node->filter('div.detail.detail-scroll')->each(function (Crawler $node, $i) {
	        global $i,$j;
	           $node->filter('div.__name > a')->each(function ($node)
	               {
	                   global $i;
	                   global $upcoming_movies_list;
	                    $upcoming_movies_list[$i]["name"] = $node->text();
	                    
	                    $link = $node->attr('href');
	                    $upcoming_movies_list[$i]["link"] = "https://in.bookmyshow.com".$link;
	                });
	            $node->filter('div.languages > ul')->each(function ($node)
	               {
	                   global $i,$j;
	                    global $upcoming_movies_list;
	                    $upcoming_movies_list[$i]["lang"] = explode(",", str_replace("/", ",", trim($node->text())));
	                    
	                });
	                $j=0;
	           $node->filter('div.genre-list > div')->each(function ($node)
	               {
	                   global $i,$j;
	                    global $upcoming_movies_list;
	                    $upcoming_movies_list[$i]["genre"][$j] = $node->text();
	                    $j+=1;
	                });
	           
	        $i +=1;
	        $j=0;
	    });
	    }
	    /**/
});
print_r($upcoming_movies_list);
$current_ts=date("Y/m/d H:i:s");

foreach($upcoming_movies_list as $movies)
{
    $movie_name = $movies["name"];
    $movie_link = $movies["link"];
    $genre=$movies["genre"];
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
				$type="upcoming";
				if($current_type=="new")
				{
					
					$prevs_type="new";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	    
	        		if(!checkLink($movie,$movie_link))
	        		updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
			        		    
				//	$events = $events_collection->insertOne(
					//array("movie_name"=>$movie_name,"lang"=>$lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FU","notify"=> 'true',"insert_ts" => $current_ts ));
				}
				elseif($current_type=="upcoming")
				{
				    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        	
	        		if(!checkLink($movie,$movie_link))
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
				}elseif($current_type=="running")
				{
				    updateMovieType($movies_collection,$movie_id,$current_type,$prevs_type,$current_ts);
	        	
	        		if(!checkLink($movie,$movie_link))
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
				}
				elseif($current_type=="closed")
				{
			    	$prevs_type="closed";
	        	    updateMovieType($movies_collection,$movie_id,$type,$prevs_type,$current_ts);
	        		
	        		if(!checkLink($movie,$movie_link))
	        	    updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
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
	        	    	$params = array("type" => "upcoming", "prev_type"=>"null","det_stat"=>"new","poster_url"=>$details["poster"],"actors"=>$details["cast"],"director"=>$details["director"],"music_director"=>$details["music"],"genre"=>$genre,"producer"=>$details["producer"],"release_ts"=>$details["release"], "disabled"=>"false", "insert_ts" => $current_ts );  
	        	    	updateMovieDetails($movies_collection,$movie_id,$params);
	        	    	updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
			            
				  //  	$events = $events_collection->insertOne(
				    //	array("movie_name"=>$movie_name, "lang" => $lang,"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FU","notify"=> 'true',"insert_ts" => $current_ts ));
			            
		            }
		        }
		        else 
		        {
		                insertMovie($movies_collection,$movie_name,$lang);
    	        	    $movie_id=isPresent($movie_name,$movies_collection,$lang);
    	        	    $params = array("type" => "upcoming", "prev_type"=>"null","det_stat"=>"new","disabled"=>"false","insert_ts" => $current_ts);  
    	        	    updateMovieDetails($movies_collection,$movie_id,$params);
    	                updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,"bms",$current_ts);
			            
						
				//		$events = $events_collection->insertOne(
				  //  	array("movie_name"=>$movie_name, "lang"=>$lang, "event_id"=>getCounter("event_id",$counter_collection),"event_type" => "FU","notify"=> 'true',"insert_ts" => $current_ts ));
			            
		        }
		        
		    }
	    
    }
}

unset($GLOBALS['upcoming_movies_list']);
//unset($upcoming_movies_list);

$current_ts=date("Y/m/d H:i:s");
echo "\n"."BookMyShow Upcoming Extract Job completed on ".$current_ts."\n";
}

bms_upcoming();
tktnew_upcoming();

$current_ts=date("Y/m/d H:i:s");
echo "Upcoming Extract Job completed on ".$current_ts."\n";
?>