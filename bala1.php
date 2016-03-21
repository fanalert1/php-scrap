<?php

//Crawler definition
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Job Started on ".$current_ts."\n";

require_once(__DIR__ . '/vendor/autoload.php');//Autoload required API's



$client = new Client();
$crawler = $client->request('GET', 'https://in.bookmyshow.com/chennai/movies/nowshowing');


//Crawler to get the upcoming movies details from ticket new website
$crawler->filter('div[id$="now-showing"]')->each(function (Crawler $node, $i){
    
    
    
    $node->filter('div[class$="__col-now-showing"]')->each(function ($node) {
    
              $node->filter('div[class$="__name"]')->each(function ($node) {
            
            
                         $node->filter('a')->each(function ($node) {
                                                       $content = $node->text();
                                                       $item = trim($content);
                                                        $link = $node->link();
                                                        $uri = $link->getUri();
                                                       echo $item."-".$uri."\n";
                         });
              });
    });   
});



?>