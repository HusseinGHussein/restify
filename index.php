<?php


require_once('Restify.php');


$r = new Restify();

$result = ($r->get("https://newsapi.org/v1/articles",array("source"=>"bbc-news","apiKey"=>"your_api_key"))->json());


$r->dd($result);

?>