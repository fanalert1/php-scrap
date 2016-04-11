<?php
require_once('../vendor/autoload.php');


//$yourApiSecret = "f14f6029e3952e2e9ccc79bbfc60fdfbb6d123497c6a35e6:";
$yourApiSecret = "03dc4c7c7df366924285ec8ed1094a5efcbe90235dac3bcb:"; //new app key
//$androidAppId = "4cff0232";
$androidAppId = "79818019";  //new app id


//$encoded='where={"notify":"true","tamil":"true","english":"true","hindi":"true","others":"true"}';
//$url='http://128.199.141.102:8080/parse/users?'.$encoded;
$url='http://128.199.141.102:8080/parse/users';
$device_tokens=get_tokens($url);
//print_r($all_device_tokens);


foreach ($device_tokens as $device_token) {
        // code...
        echo $device_token;
        $url='http://128.199.141.102:8080/parse/users/'.$device_token;
    //["objectId"]
    
     $data = array(
          "theatre" => "true"
          );
          
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Parse-Application-Id: 12345',
            'X-Parse-Master-Key: 12345'
            )
          );
        $result = curl_exec($ch);
       
echo $result;
}


function get_tokens($url)
{
    $i=0;
   // $j=0;
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
        echo $value["objectId"].", ".$value["username"].",  ".$value["email"].",  ".$value["token"]."\n";
        $device_tokens[$i]=$value["objectId"];
         $i++;
        }
        
    }
    
    return $device_tokens;
}
    
?>