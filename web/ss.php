<?php

if ( $_SERVER['REQUEST_METHOD']== "GET" ) {
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/extract_db.php';

$text = get_data();
echo $text;

}

?>
