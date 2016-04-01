<?php
require_once(__DIR__ . '/imdb_get.php');
require_once(__DIR__ . '/scrap_details.php');
require_once('../vendor/autoload.php');


use Bing\Client;


function isUpcoming($value,$collection)
{
     $count = $collection->count(["name"=>$value,"type"=>"upcoming"]);
     if($count>0)
     {
         return true;
     }
     else
     {
         return false;
     }
}
function isRunning($value,$collection)
{
     $count = $collection->count(["name"=>$value,"type"=>"running"]);
     if($count>0)
     {
         return true;
     }
     else
     {
         return false;
     }
}

function basic_clean($value)
{
    $value = strtolower($value);
   // $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value);  //this removes space also. not needed for basic clean
    $value = str_replace('(','',$value);
    $value = str_replace(')','',$value);
    $value = str_replace('-','',$value);
    $value = str_replace('2d','',$value);
    $value = str_replace('3d','',$value);
    $value = str_replace('with','',$value); // with English Subtitle
    $value = str_replace('english','',$value); // with English Subtitle
    $value = str_replace('subtitle','',$value); // with English Subtitle
    $value = str_replace('dolbyatmos','',$value);
    $value = str_replace('film','',$value); //to remove film from wiki title
    return $value;
    
}

function getSearchString($value)
{
     $value = strtolower($value);
     $value = trim($value);
     $value = str_replace(' ','',$value); // Remove white space due to space
     $value = preg_replace('/\s+/','',$value); // Remove white space due to tab
     $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value); // Removes special chars.
     $value = str_replace('-','',$value);
     $value = str_replace('2d','',$value);
     $value = str_replace('3d','',$value);
     $value = str_replace('with','',$value); // with English Subtitle
     $value = str_replace('english','',$value); // with English Subtitle
     $value = str_replace('subtitle','',$value); // with English Subtitle
     $value = str_replace('dolbyatmos','',$value);
     $value = str_replace('film','',$value);
     return $value;
}

function isPresent($value,$collection,$lang)
{
     $id="";
     $search_string = getSearchString($value);
     $movie = $collection->findOne(["search_string"=>$search_string,"lang"=>$lang]);
     $id = $movie['_id'];
     if($id!="")
     {
         echo "\nMovie Found";
         return $id;
     }
     else
     {
        // return false;
        echo "\nMovie not found. going for pattern match";
        return pattern_match($search_string,$lang,$collection);
     }
}


//{_id:ObjectId("56f517c1f7478630384e7ec5")}

function pattern_match($search_string,$lang,$collection)
{
  //  db.movies.find({"search_string":{ $regex: 'an$'}})
   // db.movies.find({"search_string":{ $regex: '^pi'}})
     $id="";
     $first = substr($search_string, 0, 2);
     $last = substr($search_string, strlen($search_string)-2, strlen($search_string));
     echo "\n".$first." ".$last."\n";
     $pattern = "^".$first.".+".$last."$"; //'^ba.+ce$'
     //echo $pattern;
     $movie = $collection->findOne(["search_string" => [ '$regex' => $pattern],"lang"=>$lang]);
     
     similar_text($search_string, $movie['search_string'], $p); 
     
     if($p>80)
     {
     $id = $movie['_id'];
     }
     return $id;
     //db.movies.find({"search_string":{ $regex: '^ba.+ce$'}})
}



function getDetail($id,$collection,$inField,$outField)
{
    $movie = $collection->findOne(['_id' => $id]);
    return $movie; //returns the entire movie array. field choosing can be done in main script
}

function checkLink($movie,$link)
{
    // $links=array();
     $flag=false;
     foreach($movie["source"] as $source) 
          {
              if($source["link"]==$link)
              {
                $flag=true;
              }      
              /* foreach($source as $key=>$value)
               {
                    if ($link==$value["link"])
                    {
                        $flag=true;
                    }
               }
              */
          }
    return $flag;
}

function checkSourceLink($movie,$link)
{
     $flag=false;
     foreach($movie["det_source"] as $source) 
          {
               foreach($source as $key=>$value)
               
               {
                    if ($link==$value["link"])
                    {
                        $flag=true;
                    }
                   
                }
          }
    return $flag;
}

function getCounter($name,$collection)
{
    $test = $collection->findOne(["name"=>$name]);
    $temp = json_encode($test);
    $json = json_decode($temp , true);
    $test = $collection->updateOne(["name"=>$name],
    ['$set' => array("count"=> $json["count"]+1)],
    ['upsert' => false]);
    return $json["count"];
}


function getMovieDetails($movie_name,$movie_link,$lang,$callFrom)
{
   //unset($movie_details);
   //reset($movie_details);
   unset($movie_details);
   $movie_details=array();
   $movie_name=basic_clean($movie_name);
   
   $imdb_url =  bingSearch($movie_name,$lang,"imdb");
   if($imdb_url!="")
   {
       
       $movie_details=get_imdb_det($movie_name);
       echo $imdb_url."\n";
       //print_r($movie_details);
       if(count($movie_details)==1 && array_key_exists("director", $movie_details[0]) && !empty($movie_details[0]["director"]) && $movie_details[0]["poster"]!="" )
       {
       return $movie_details;
       }
   }
   
   
   
   $wiki_url = bingSearch($movie_name,$lang,"wiki");
   //$imdb_url =  bingSearch($movie_name,$lang,"imdb");
  
   //  echo $result."\n";
   if($wiki_url!="")
   {
       unset($movie_details);
       $movie_details=array();
       $movie_details[0]=wiki_scrap($movie_name,$wiki_url);
       
       
     //  if($movie_details[0]["wiki_title"]!="")
     //  {
           similar_text($movie_name, basic_clean($movie_details[0]["wiki_title"]), $p); 
           if($p<80)
           {
              // $lang="2016 ".$lang;
              echo "wiki title not matching";
               $wiki_url = bingSearch($movie_name,$lang,"wiki","2016");
               $movie_details[0]=wiki_scrap($movie_name,$wiki_url);
           }
       
       
           if(array_key_exists("director", $movie_details[0]) && is_null($movie_details[0]["director"]))
           {
               echo "call1";
               $wiki_url = bingSearch($movie_name,$lang,"wiki","2016");
               $movie_details[0]=wiki_scrap($movie_name,$wiki_url);
           }
           else if(!array_key_exists("director", $movie_details[0]))
           {
               echo "call2";
                $wiki_url = bingSearch($movie_name,$lang,"wiki","2016");
                $movie_details[0]=wiki_scrap($movie_name,$wiki_url);
               
           }
           if($callFrom=="tktnew")
           {
           $release_ts=date("Y/m/d H:i:s",strtotime(tktnew_scrap($movie_name,$movie_link)));
           $movie_details[0]["release"]=$release_ts;
           }
       
      return $movie_details; 
     // }
   
   }
   
   $fb_url =  bingSearch($movie_name,$lang,"filmibeat");
   if($filmibeat!="")
   {
       unset($movie_details);
       $movie_details=array();
       $movie_details[0]=filmibeat_scrap($movie_name);
       echo "\nFound in filmi"."\n";
       print_r($movie_details);
       return $movie_details;
   }
   
    
}


function linkcheck($url)
{
    $handle = curl_init($url);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if($httpCode == 200) 
    {
        curl_close($handle);
        return true;
    }else
    {
        curl_close($handle);
        
        $data = "payload=" . json_encode(array(
                "username"=>  "LinkCheck",
                "channel"       =>  "#alerts",
                "text"          =>  "{$url} - getting response code - {$$httpCode}"
            ));
        $ch = curl_init('https://hooks.slack.com/services/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');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_close($ch);
        $result = curl_exec($ch);
        return false;
    }
}


function  updateMovieType($movies_collection,$movie_id,$type,$prev_type,$current_ts)
{
    	$result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$set' => array("type" => $type, "prev_type" => $prev_type, "update_ts" => $current_ts )],
	        		['upsert' => false]
	        		);
}


function  updateMovieBookingLinks($movies_collection,$movie_id,$movie_name,$movie_link,$source,$current_ts)
{
    
    //	['$addToSet' => array("source"=> array($source => array("title"=>$movie_name,"link"=>$movie_link,"booking_open_ts"=>$current_ts)))],
    
    $result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$addToSet' => array("source"=> array("source"=>$source,"title"=>$movie_name,"link"=>$movie_link,"booking_open_ts"=>$current_ts))],
	        		['upsert' => false]
	        		);
}

function  updateMovieDetailsLinks($movies_collection,$movie_id,$movie_name,$movie_link,$source)
{
    
    $result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$addToSet' => array("det_source"=> array($source => array("title"=>$movie_name,"link"=>$movie_link)))],
	        		['upsert' => false]
	        		);
}

function insertMovie($movies_collection,$movie_name,$lang)
{
    $result = $movies_collection->insertOne(
	        	    	array("lang" => $lang,"name" => $movie_name,"search_string" => getSearchString($movie_name)));
	        	    	
}


function updateMovieDetails($movies_collection,$movie_id,$params)
{
    $result = $movies_collection->updateOne(
	        		['_id' => $movie_id],
	        		['$set' => $params],
	        		['upsert' => false]
	        		);
}

function enrichMovieDetails($movie_name,$url,$source)
{
    $movie_details=array();
    switch ($source) {
    case "tktnew":
          $movie_details=tktnew_scrap($movie_name,$url);
        break;
    case "blue":
        echo "Your favorite color is blue!";
        break;
    case "green":
        echo "Your favorite color is green!";
        break;
    default:
        echo "Your favorite color is neither red, blue, nor green!";
    }
    
    return $movie_details;

}

function googleApiSearch($movie_name,$search_type)
{
    $movie_links=array();
    $search_site = array("wiki","imdb","themoviedb","filmibeat");
    $google_url="http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=";
    if($search_type=="trailer")
    {
        $search_string= urlencode($movie_name." ".date("Y")." trailer");
        $ch = curl_init($google_url.$search_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $temp = json_decode($result, true );
        $array = $temp["responseData"]["results"];
        $movie_links["trailer"] = $temp["responseData"]["results"][0]["unescapedUrl"];
    }else
    {
        foreach ($search_site as $value) 
        {
            //sleep(10);
            $search_string= urlencode($movie_name." ".date("Y")." film ".$value);
         //  $search_string= urlencode($movie_name." ".$value);
            $ch = curl_init($google_url.$search_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $temp = json_decode($result, true );
            $array = $temp["responseData"]["results"];
            
            $movie_links[$value] = $temp["responseData"]["results"][0]["unescapedUrl"];
            echo "\n$value:\n";
            foreach ($temp["responseData"]["results"] as $result) 
            {
              // $movie_links[]=$value["unescapedUrl"];
              
              echo $result["unescapedUrl"]."\n";
            }
        }
    }
    print_r($movie_links);
    
    return $movie_links;
}


function bingSearch($movie_name,$lang,$source,$year="")
{
    
    
    if($source=="wiki")
    {
        if($year!="")
            $query=$movie_name." ".$year." film wikipedia";
        else
            $query=$movie_name." film wikipedia";
            
        $site="https://en.wikipedia.org";
        $no="";
    }
    else if($source=="imdb")
    {
    $query=$movie_name." film imdb";
    $site="http://www.imdb.com/title/";
    $no="";
    }
    else if($source=="filmibeat")
    {
    $query=$movie_name." filmibeat";
    $site="http://www.filmibeat.com/tamil/movies/";
    //$no="6";
    }
    
    
    // You need to obtain a key
    $key = 'jUS+BJ/08ISxlwQ2rlfM2sCH8X8lNuP9qYXectFpz0w';
    $c = new Client($key, 'json');
    $res = $c->get('Web', array('Query' => $query));
    $res = json_decode($res, true);
    $url = "";
    
    echo $query;
    
    foreach($res["d"]["results"] as $result)
    {
    
         $url = $result["Url"];
        // echo $url."\n";
         if (strpos($url, $site) !== false) 
         {
                
                if($source=="filmibeat")
                {
                    if(substr_count($url, '/')==6)
                     {
                         
                         $result=explode("/",$url);
                         $url=$result[0]."//".$result[2]."/".$result[3]."/".$result[4]."/".$result[5].".html";
                         return $url;
                         // echo "true";
                     }
                }
                else                
                return $url;
         }
     
    }
    return $url;
}

?>
