<?php 

require_once('../vendor/autoload.php');

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
$cast_crew=array();
$movie="";
$field="";
$info="";
$info1=array();
function tktnew_scrap($name,$movie_link)
{
    $movie_name = $lang = $actor = $movie_id = $director = $music = $key="";
    
    unset($GLOBALS['cast_crew']);
    global $cast_crew;
    $cast_crew = array();
    $temp_id=explode("/",$movie_link);
    $temp_name=str_replace(" ","-",$name); //temporary variable to get the link of the movie from the array
    $movie_id=$temp_id[5];
    $poster_url="http://cdn.in.ticketnew.com/Movie/".$movie_id."/m1.jpg";
    
    $client = new Client();
    $crawl_link="http://www.ticketnew.com/".$temp_name."-Movie-Tickets-Online-Show-Timings/Online-Advance-Booking/".$temp_id[5]."/C/Chennai";
    $crawl_link = $movie_link;
    $crawler = $client->request('GET', $crawl_link);
    
    //Crawler to get the synopsis of the movies
    $crawler->filter('div[class$="movie-info-synopsis"]')->each(function (Crawler $node, $i) {
        $node->filter('td')->each(function ($node) {
            global $cast_crew;
            global $key;
            
            $value= $node->text();
            $temp=explode('\n',$value);
            foreach($temp as $values)
            {
                $value=trim($values);
                if($value=="Genre")
                {
                    $key=$value;
                }
                elseif($value=="Language")
                {
                    $key=$value;
                }
                elseif($value=="Movie Producer")
                {
                    $key="Producer";
                }
                elseif($value==":")
                {
                }
                elseif(strpos($value, "Release") !== false)
                {
                    $key="Release";
                }
                else
                {
                    $cast_crew[$key][]=$value;
                }
            }
        });
    });
    $genre=explode(",",$cast_crew["Genre"][0]);
    $producer=explode(",",$cast_crew["Producer"][0]);
    $release_ts=$cast_crew["Release"][0];
    
    //Crawler to get the cast and crew details
    $crawler->filter('div[class$="movie-info-description"]')->each(function (Crawler $node, $i) {
        $node->filter('p')->each(function ($node) {
            global $cast_crew;
            $value= $node->text();
            $temp=explode('\n',$value);
            global $key;
            foreach($temp as $value)
            {
                $first=explode(':',$value);
                foreach($first as $value)
                {
                    $value=trim($value);
                    
                   if($value=="Actors")
                   {
                       $key=$value;
                   }
                   elseif($value=="Director")
                   {
                       $key=$value;
                       //echo $value."\n";
                   }
                   elseif($value=="Music director")
                   {
                       $key=$value;
                   }
                   else
                   {
                       $cast_crew[$key][]=$value;
                   }
                }
            }
        });
    });
    $actor=explode(",",$cast_crew["Actors"][0]);
    $director=explode(",",$cast_crew["Director"][0]);
    $music=explode(",",$cast_crew["Music director"][0]);
    unset($GLOBALS["cast_crew"]);
    unset($GLOBALS["key"]);
    $movie_details=array();
    $movie_details[$name]["cast"]=$actor;
    $movie_details[$name]["director"]=$director;
    $movie_details[$name]["producer"]=$producer;
    $movie_details[$name]["music"]=$music;
    $movie_details[$name]["genre"]=$genre;
    $movie_details[$name]["poster"]=$poster_url;
    $movie_details[$name]["release"]=$release_ts;
    
   // print_r($movie_details);
   return $movie_details[$name]; //fix for array stacking issue
   //return $release_ts;
    
}

function wiki_scrap($name,$url)
{
    global $movie;
    //fix for duplicate issue
    global $cast_crew;
   // unset($GLOBALS["cast_crew"]);
    //unset($cast_crew);
    //$cast_crew=array();
    
    
    $movie=$name;
    $client = new Client();
    $crawler = $client->request('GET', $url);
        $crawler->filter('table.infobox.vevent')->each(function (Crawler $node, $i) 
        {
            $node->filter('tr')->each(function (Crawler $node, $i) 
            {
                global $cast_crew;
                global $movie;
                global $field;
                global $info;
                global $info1;
                $node->filter('th[class$="summary"]')->each(function ($node) {
                                 global $field;
                                 global $info;
                                 $field="title";
                                 $info=trim($node->text());
                                // echo $info;
                                // $cast_crew[$movie]["wiki_title"]=trim($node->text());
                                 //$field=trim($node->text());
                            });
                
                $node->filter('th')->each(function ($node) {
                                 global $field;
                                 if($field!="title")
                                 {
                                 $field=trim($node->text());
                                 }
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
                
                if ($field=="title") {
                    $cast_crew[$movie]["wiki_title"]= $info;
                }
                elseif ($field=="Directed by") {
                    $cast_crew[$movie]["director"]= empty($info) ? $info1 : $info;
                }elseif ($field=="Produced by") {
                    $cast_crew[$movie]["producer"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Written by") {
                    $cast_crew[$movie]["writer"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Starring") {
                    $cast_crew[$movie]["cast"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Music by") {
                    $cast_crew[$movie]["music"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Release dates") {
                    $cast_crew[$movie]["release"]=empty($info) ? $info1 : $info;
                }elseif ($field=="Language") {
                    $cast_crew[$movie]["lang"]=empty($info) ? $info1 : $info;
                }elseif ($field=="poster") {
                    $cast_crew[$movie]["poster"]=empty($info) ? $info1 : $info;
                    $cast_crew[$movie]["poster"]="http:".$cast_crew[$movie]["poster"];
                }
            });
            
        });
        $crawler->filter('p:nth-child(2)')->each(function (Crawler $node, $i) {
            global $cast_crew;
            global $movie;
            $cast_crew[$movie]["synopsis"]=$node->text();
            
        });

$movie_details = $cast_crew[$movie];//fix to return that movie alone
unset($GLOBALS["cast_crew"]);
unset($GLOBALS["movie"]);
unset($GLOBALS["field"]);
unset($GLOBALS["info"]);
unset($GLOBALS["info1"]);

return $movie_details;
}

function bms_scrap($movie_name,$movie_link)
{
    global $key, $cast_crew;
    $movie_details= array();
    //unset($GLOBALS["cast_crew"]);
    //unset($GLOBALS["key"]);
    $client = new Client();
	$crawler = $client->request('GET', $movie_link);
	$crawler->filter('body > div.main-body-wrapper > div.movie-synopsis-content-wrapper > div.mv-synopsis-wrapper > div.details > div.general-info > div.name-rating > h1.__name')->each(function (Crawler $node, $i) {
	    global $key;
	    $key = $node->text();
	});
	$movie_details["name"]=$key;
	$crawler->filter('body > div.main-body-wrapper > div.movie-synopsis-content-wrapper > div.mv-synopsis-wrapper > div.details > div.general-info > div.date-time > div.calander-date > span.__release-date')->each(function (Crawler $node, $i) {
	    global $key;
	    $key = $node->text();
	});
	$movie_details["release_ts"]=$key;
	//unset($GLOBALS["key"]);
	$crawler->filter('body > div.main-body-wrapper > div.movie-synopsis-content-wrapper > div.mv-synopsis-wrapper > div.poster-container-wrapper > div > div.poster.wow > img')->each(function (Crawler $node, $i) {
	    global $key;
	    $key = "https:".$node->attr('data-src');
	});
	$movie_details["poster_url"]=$key;
	//unset($GLOBALS["key"]);
	$crawler->filter('#mv-summary > div.cast')->each(function (Crawler $node, $i) {
	    $node->filter('div.__cast-member')->each(function ($node) {
	        global $cast_crew;
	        $cast_crew[]=$node->attr('content');
	    });
	});
	$movie_details["cast"]=$cast_crew;
	//unset($GLOBALS["cast_crew"]);
	$crawler->filter('#mv-summary > div.synopsis')->each(function (Crawler $node, $i) {
	    $node->filter('blockquote')->each(function ($node) {
	        global $cast_crew;
	        $cast_crew["synopsis"]=trim($node->text());
	    });
	    $node->filter('span.__director-name > a')->each(function ($node) {
	        global $cast_crew;
	        $cast_crew["director"][]=$node->text();
	    });
	    $node->filter('span.__composer-name > a')->each(function ($node) {
	        global $cast_crew;
	        $cast_crew["music"][]=$node->text();
	    });
	});
	$movie_details["synopsis"]=$cast_crew["synopsis"];
	$movie_details["director"]=$cast_crew["director"];
	$movie_details["music"]=$cast_crew["music"];
	//print_r($movie_details);
	unset($GLOBALS["cast_crew"]);
	unset($GLOBALS["key"]);
	return $movie_details;
	    
}

//bms_scrap("https://in.bookmyshow.com/chennai/movies/the-jungle-book-3d/ET00034007");

?>