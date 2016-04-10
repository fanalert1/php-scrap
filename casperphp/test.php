<?php
require_once(__DIR__ . '/src/Browser/Casper.php');
use Browser\Casper;

$casper = new Casper();

// forward options to phantomJS
// for exemple to ignore ssl errors
$casper->setOptions(array(
    'ignore-ssl-errors' => 'yes'
));

// navigate to google web page
$casper->start('http://www.ticketnew.com/Theri-Movie-Tickets-Online-Show-Timings/Online-Advance-Booking/12892/C/Chennai');



// wait for 5 seconds (have a cofee)
$casper->wait(5000);


       
// run the casper script
$casper->run();



// need to debug? just check the casper output
var_dump($casper->getOutput());

?>