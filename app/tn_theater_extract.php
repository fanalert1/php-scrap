<?php
require_once('../casperphp/src/Browser/Casper.php');
require_once('../vendor/autoload.php');

require_once('config/db.php');
require('global_function.php');

use Browser\Casper;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;


$current_ts=date("Y/m/d H:i:s");
echo "Job Started on ".$current_ts."\n";

$theatres=array();

$casper = new Casper();

$casper->start('http://www.ticketnew.com/Theri-Movie-Tickets-Online-Show-Timings/Online-Advance-Booking/12892/C/Chennai');
//$casper->wait(15000);
$casper->waitForSelector('.theater-name', 15000);
//E[foo="bar"]
//span[class="example"]

$casper->run();
$html = $casper->getHTML();

//echo $html;
$crawler = new Crawler();
$crawler->addHtmlContent($html);
$scrape=array();
$upcoming_movies_links=array();

$crawler->filter('div.theater-name')->each(function (Crawler $node, $i) {
global $theatres;
//echo(trim($node->text()))."\n";
$theatres[]=trim($node->text());


});
print_r($theatres);
foreach ($theatres as $theatre)
{
    
    createTheaterEvent($events_collection,$counter_collection,"570392b6f7478646a55a1a4f","Theri","Tamil",$theatre,"tktnew");
    
}

?>