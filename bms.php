<?php

//Crawler definition
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Job Started on ".$current_ts."\n";

require_once(__DIR__ . '/vendor/autoload.php');//Autoload required API's



$client = new Client();
$crawler = $client->request('GET', 'http://www.ticketnew.com/Movie-Ticket-Online-booking/C/Chennai');

$upcoming_movies_list=array();
$upcoming_movies_links=array();
$active_movies=array();
$key="";
$i=0;
$link_count=0;
//Crawler to get the upcoming movies details from ticket new website
$crawler->filter('div[id$="overlay-tab-booking-open"]')->each(function (Crawler $node, $i) {

        
                    

                         $node->filter('h3,li')->each(function ($node) {
                                   //print_r($node->link());
                                   $content = $node->text();
                                   $item = trim($content);
                                   echo $item;
                                   //echo $content;
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
                                    else{
                                        
                                        if(!in_array($item, $upcoming_movies_list[$key]))
                                        {
                                            $upcoming_movies_list[$key][] = $item;
                                          //  global $link_count;
                                        //    $link_count++;
                                            $node->filter('a')->each(function (Crawler $node){
                                                global $upcoming_movies_links;
                                                $link = $node->link();
                                                $uri = $link->getUri();
                                                global $key;
                                                $upcoming_movies_links[$key][] = $uri;
                                            });
                                        
                                        
                                        
                                        
                                        }
                                    }
                                    
                                  
                                    
                         });
                //to get link for respective language movies
             

        });
        
        

print_r($upcoming_movies_list);
print_r($upcoming_movies_links);
//echo $link_count;


foreach($upcoming_movies_list as $key=>$values)
    {
        $lang=$key; //sets language as key of the array
        foreach ($values as $key => $value)
        {
            $cast_crew=array();
            $movie_name=$value; // sets movie name
            $active_movies[$i]=$movie_name;
            $i +=1;
            $temp_name=str_replace(" ","-",$movie_name); //temporary variable to get the link of the movie from the array
            $movie_link=$upcoming_movies_links[$lang][$key];
            echo $movie_link;
        }
    }
?>