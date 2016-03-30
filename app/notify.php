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

$i=0;
$device_tokens=array();

/*$token = $tokens_collection->find();
foreach($token as $document)
{
  $temp = json_encode($document);
  $json = json_decode($temp , true);
  $device_tokens[$i]=$json["token_id"];
  $i +=1;
}*/
//$ch = curl_init('https://parse-androbala.c9users.io/parse/users');
$ch = curl_init('http://128.199.141.102:8080/parse/users');

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
        $device_tokens[$i]=$value["token"];
        $i +=1;
        }
        
    }

$event_type="";
$not_title="";
$events = $events_collection->find(array("notify" =>"true"));

foreach($events as $document)
{

    $temp = json_encode($document);
    $json = json_decode($temp , true);
   // print_r($json);
    echo $document["_id"];
    $not_title = $json["movie_name"]." (Tamil)";
    $movie_id = (string)$document["_id"];
    $movie_name = $json["movie_name"];
    if($json["event_type"]=="RC")
    {
      $event_type="Booking closed on ticketnew.com";
    }
    elseif($json["event_type"]=="FU")
    {
      $event_type=$json["movie_name"]." is releasing soon";
    }
    elseif($json["event_type"]=="UR")
    {
      $event_type="Booking opened on ticketnew.com";
    }
    elseif($json["event_type"]=="FR")
    {
      $event_type="Booking opened on ticketnew.com";
    }

    $data = array(
      "tokens" => $device_tokens,
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
    echo $data_string."\n";
    echo $result."\n";
    $test = $events_collection->updateOne(
            ['event_id' => $json["event_id"]],
            ['$set' => array("notify"=>"done","notified_ts" => $current_ts)],
            ['upsert' => true]);
}


echo "Job completed on ".$current_ts."\n";

?>