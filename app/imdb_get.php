<?php
require_once('../vendor/autoload.php');


function get_imdb_det_id($id)
{
    $movie = new \Imdb\Title($id);
    $movie_list[0]["name"] = $movie->title();
    $movie_list[0]["imdbid"] = $movie->imdbid();
    $movie_list[0]["synopsis"] = $movie->plotoutline();
    $movie_list[0]["poster"]=$movie->photo();
    $movie_list[0]["lang"]=$movie->languages();
    $movie_list[0]["director"]=get_det_arr($movie->director());
    $movie_list[0]["producer"]=get_det_arr($movie->producer());
    $movie_list[0]["music"]=get_det_arr($movie->composer());
    $movie_list[0]["cast"]=get_det_arr($movie->cast());
    $movie_list[0]["release"]=get_release($movie->releaseInfo());
    return $movie_list;
}

function get_imdb_det($movie_name)
{
    $year = date("Y");
    $search = new \Imdb\TitleSearch(); 
    $movie_n = $movie_name.' '.$year;
    $results = $search->search($movie_n, [\Imdb\TitleSearch::MOVIE]);
    $i=0;
    $movie_list=array();
    foreach ($results as $result) 
        {
            $movie = new \Imdb\Title($result->imdbid());
            $movie_list[$i]["name"] = $movie->title();
            $movie_list[$i]["imdbid"] = $result->imdbid();
            $movie_list[$i]["synopsis"] = $movie->plotoutline();
            $movie_list[$i]["poster"]=$movie->photo();
            $movie_list[$i]["lang"]=$movie->languages();
            $movie_list[$i]["director"]=get_det_arr($movie->director());
            $movie_list[$i]["producer"]=get_det_arr($movie->producer());
            $movie_list[$i]["music"]=get_det_arr($movie->composer());
            $movie_list[$i]["cast"]=get_det_arr($movie->cast());
            $movie_list[$i]["release"]=get_release($movie->releaseInfo());
            $i+=1;
        }
return $movie_list;
    
}
function get_release($arraylist)
{
    $release_dt="";
    foreach($arraylist as $key=>$values)
    {
        if($values["country"]=="India")
        {
            $release_dt=$values["year"].'/'.$values["mon"].'/'.$values["day"];
        }
    }
    
    return $release_dt;   
}
function get_det_arr($arraylist)
{
    
    $ret_array=array();
    foreach($arraylist as $key=>$values)
    {
        array_push($ret_array,$values["name"]);
    }
    
    return $ret_array;
}


//$json=get_imdb_det("Kadhalum");

//print_r($json);
?>