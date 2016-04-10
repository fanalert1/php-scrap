<?php
require_once('../vendor/autoload.php');
require_once('imdb_get.php');
require_once('config/db.php');
require('global_function.php');


date_default_timezone_set("Asia/Calcutta");//Set timezone to India
$current_ts=date("Y/m/d H:i:s");
echo "Job Started on ".$current_ts."\n";



//Snippet for updating the closed movies and inserting events

$result = $movies_collection->find(array('type' => array('$in' => array("running","upcoming"))));
$i=0;
$current=date("Y/m/d H:i:s");

foreach($result as $key=> $document)
{
    $temp = json_encode($document);
    $json = json_decode($temp , true);

    $update=date_create($json["update_ts"]);
    $current=date_create(date("Y/m/d H:i:s"));
    $diff=date_diff($update,$current);
    $difference=$diff->format("%h");

//should update prev type of closed movies also

    if($difference>=12)
    {
    $type="closed";
        $movies = $movies_collection->updateOne(
            ['name' => $json["name"]],
            ['$set' => array("type" => $type, "disabled"=>'true', "close_ts" => $current_ts)],
            ['upsert' => true]);

        $events = $events_collection->insertOne(
            array("movie_id"=>$json["id"],"movie_name"=>$json["name"],"event_id"=>getCounter("event_id",$counter_collection),"event_type" => "RC","notify"=> 'true',"insert_ts" => $current_ts ));

    }
}
//Notification for the new events added

//$yourApiSecret = "f14f6029e3952e2e9ccc79bbfc60fdfbb6d123497c6a35e6:";
$yourApiSecret = "03dc4c7c7df366924285ec8ed1094a5efcbe90235dac3bcb:"; //new app key
//$androidAppId = "4cff0232";
$androidAppId = "79818019";  //new app id



/*$token = $tokens_collection->find();
foreach($token as $document)
{
  $temp = json_encode($document);
  $json = json_decode($temp , true);
  $device_tokens[$i]=$json["token_id"];
  $i +=1;
}*/
//$ch = curl_init('https://parse-androbala.c9users.io/parse/users');


//$encoded=urlencode('where={"notify":"true","tamil":"true","english":"true","hindi":"true","others":"true"}');
$encoded='where={"notify":"true","tamil":"true","english":"true","hindi":"true","others":"true"}';
$url='http://128.199.141.102:8080/parse/users?'.$encoded;
$all_device_tokens=get_tokens($url);
print_r($all_device_tokens);

$encoded='where={"notify":"true","tamil":"true"}';
$url='http://128.199.141.102:8080/parse/users?'.$encoded;
$tamil_device_tokens=get_tokens($url);
print_r($tamil_device_tokens);

$encoded='where={"notify":"true","english":"true"}';
$url='http://128.199.141.102:8080/parse/users?'.$encoded;
$english_device_tokens=get_tokens($url);
print_r($english_device_tokens);

$encoded='where={"notify":"true","hindi":"true"}';
$url='http://128.199.141.102:8080/parse/users?'.$encoded;
$hindi_device_tokens=get_tokens($url);
print_r($hindi_device_tokens);

$encoded='where={"notify":"true","others":"true"}';
$url='http://128.199.141.102:8080/parse/users?'.$encoded;
$others_device_tokens=get_tokens($url);
print_r($others_device_tokens);


function get_tokens($url)
{
    $i=0;
    $j=0;
    $device_tokens=array();
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'X-Parse-Application-Id: 12345',
        'X-Parse-Master-Key: 12345'
        )
    );
    $token_api_result = json_decode(json_encode(curl_exec($ch)));
    //$token_api_json=json_decode(json_encode($token_api_result),true);
    $token_api_array=json_decode($token_api_result,true);
    
    foreach($token_api_array["results"] as $value)
    {
        if(!($value["token"]=="")){
        //echo $value["username"].",  ".$value["email"].",  ".$value["token"]."\n";
        $device_tokens[$i][$j]=$value["token"];
            if($j==49)
            {
            $j=0;
            $i+=1;
            }
            else
            {
            $j+=1;
            } 
        }
        
    }
    
    return $device_tokens;
}
//process events to the device tokens

$event_type="";
$not_title="";
$events = $events_collection->find(array("notify" =>"true"));

foreach($events as $document)
{

    $temp = json_encode($document);
    $json = json_decode($temp , true);
   // print_r($json);
    echo $document["_id"];
    $lang =  $json["lang"];
    $not_title = $json["movie_name"]." (".$lang.")";
    $movie_id = (string)$document["movie_id"];
    $movie_name = $json["movie_name"];
    
    if($json["opened_at"]=="tktnew")
    $booking_site="ticketnew.com";
    elseif($json["opened_at"]=="bms")
    $booking_site="bookmyshow.com";
    
    if($json["event_type"]=="RC")
    {
      $event_type="Booking closed on ".$booking_site;
    }
    elseif($json["event_type"]=="FU")
    {
      $event_type="Get Ready. ".$json["movie_name"]." is releasing soon";
    }
    elseif($json["event_type"]=="UR")
    {
      $event_type="Booking opened on ".$booking_site;
    }
    elseif($json["event_type"]=="FR")
    {
      $event_type="Booking opened on ".$booking_site;
    }
     elseif($json["event_type"]=="RR")
    {
      $event_type="Booking opened on ".$booking_site;
    }
    
    $device_tokens=array();
    
    if($lang=="Tamil")
    $device_tokens=$tamil_device_tokens;
    elseif($lang=="English")
    $device_tokens=$english_device_tokens;
    elseif($lang=="Hindi")
    $device_tokens=$hindi_device_tokens;
    elseif($lang=="Malayalam"||$lang=="Telugu"||$lang=="Kannada")
    $device_tokens=$others_device_tokens;
    
    foreach ($device_tokens as $device_tokens_batch) {
        // code...
    
        $data = array(
          "tokens" => $device_tokens_batch,
          "notification" => ["alert"=>$event_type,"android"=>["title" => $not_title,"notId"=>$json["event_id"],"payload"=>["image"=>"icon","title" => $not_title,"message" => $event_type,"movie_id" => $movie_id, "movie_name" => $movie_name]]]
            );
        $data_string = json_encode($data);
        $ch = curl_init('https://push.ionic.io/api/v1/push');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Ionic-Application-Id: '.$androidAppId,
            'Content-Length: ' . strlen($data_string),
            'Authorization: Basic '.base64_encode($yourApiSecret)
            )
        );
        $result = curl_exec($ch);
        echo "BATCH 1";
        print_r($device_tokens_batch);
        echo $data_string."\n";
        
    } 
   // echo $result."\n";
    $test = $events_collection->updateOne(
            ['event_id' => $json["event_id"]],
            ['$set' => array("notify"=>"done","notified_ts" => $current_ts)],
            ['upsert' => true]);
}


echo "Job completed on ".$current_ts."\n";

?>