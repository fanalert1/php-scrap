

<?php


/*

http://stackoverflow.com/questions/11989959/bing-api-authorization-not-working

https://api.datamarket.azure.com/Bing/Search/Web?$format=json&Query=%27Theri%20wiki%27

https://api.datamarket.azure.com/Bing/Search/Web?$format=json&Query=%27Theri%27wikipedia%27

First name Balagovindan Last name Veeraragavan Organization BMS Apps E-mail address fanalert1@gmail.com Country / Region United States Language English
I agree that Microsoft may use my email address to provide information and offers regarding Microsoft Azure Marketplace.
Primary Account Key	jUS+BJ/08ISxlwQ2rlfM2sCH8X8lNuP9qYXectFpz0w
Customer ID	9b1c6673-f921-485a-8612-124858cb80ae

*/




/****

* Simple PHP application for using the Bing Search API

*/

$acctKey = '9b1c6673-f921-485a-8612-124858cb80ae';


https://user:yourAccountKey@api.datamarket.azure.com/Bing/SearchWeb/Web?Query=%27leo%20fender%27&Market=%27en-US%27&$top=50&$format=JSON">


$rootUri = 'https://api.datamarket.azure.com/Bing/Search';

// Read the contents of the .html file into a string.

//$contents = file_get_contents('bing_basic.html');

// Encode the query and the single quotes that must surround it.

//$query = urlencode("'{$_POST['query']}'");
$query = "%27Theri%27";

// Get the selected service operation (Web or Image).

//$serviceOp = $_POST['service_op'];

$serviceOp = "Web";

// Construct the full URI for the query.

$requestUri = "$rootUri/$serviceOp?\$format=json&Query=$query";


// Encode the credentials and create the stream context.

$auth = base64_encode("$acctKey:$acctKey");

//echo $auth;
echo $auth;


$data = array(

'http' => array(

'request_fulluri' => true,

// ignore_errors can help debug – remove for production. This option added in PHP 5.2.10

'ignore_errors' => true,

'header' => "Authorization: Basic $auth")

);

$context = stream_context_create($data);

// Get the response from Bing.

$response = file_get_contents($requestUri, 0, $context);

echo $response;


$jsonObj = json_decode($response); $resultStr = ''; // Parse each result according to its metadata type. 

foreach($jsonObj->d->results as $value) 

{ 
    
    switch ($value->__metadata->type) 
    { 
        case 'WebResult': 
        $resultStr .= "<a href=\"{$value->Url}\">{$value->Title}</a><p>{$value->Description}</p>"; 
        break; 
        
        case 'ImageResult': 
        $resultStr .= "<h4>{$value->Title} ({$value->Width}x{$value->Height}) " . "{$value->FileSize} bytes)</h4>" . "<a href=\"{$value->MediaUrl}\">" . "<img src=\"{$value->Thumbnail->MediaUrl}\"></a><br />"; 
        break; 
        
    } 
    
} // Substitute the results placeholder. Ready to go. 


//$contents = str_replace('{RESULTS}', $resultStr, $contents);


print_r( $resultStr);

//echo $contents;


 

//$auth = base64_encode("username:$acctKey");

//$credentials = "username:$acctKey";
$credentials = "$acctKey:$acctKey";
$cred=base64_encode($credentials);
//echo $auth;

/*

CURLOPT_USERPWD basically sends the base64 of the user:password string with http header like below:

Authorization: Basic dXNlcjpwYXNzd29yZA==
*/

$headers = array(
                    "Authorization: Basic " . base64_encode($credentials)
                );
$url = 'https://api.datamarket.azure.com/Bing/Search/Web?$format=json&Query=%27Theri%27';
$ch = curl_init($url);
            //curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
               // curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
              //  curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
           curl_setopt($ch, CURLOPT_USERPWD,  $credentials);

                $rs = curl_exec($ch);
          //  print_r($rs);

# Deliver
//print_r ($rs);

# Have a great day!
//curl_close($process);



//$result = curl_exec($ch);


//print_r($result);



?>


