<?php 

require_once('../vendor/autoload.php');//Autoload required API's

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Upcoming Extract Job Started on ".$current_ts."\n";

$upcoming_movies_list=array();
$upcoming_movies_links=array();

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

print_r($upcoming_movies_list);
print_r($upcoming_movies_links);

$current_ts=date("Y/m/d H:i:s");
echo "Upcoming Extract Job completed on ".$current_ts."\n";

?>