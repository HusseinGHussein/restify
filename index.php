<?php


require_once('Restify.php');


$r = new Restify();

$result = ($r->get("http://www.thomas-bayer.com/sqlrest/CUSTOMER/")->xml());

var_dump($result);

?>