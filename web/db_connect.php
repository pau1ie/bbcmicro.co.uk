<?php
$dbhost="localhost";
$dbname='bbc';
$dbuser='bbc';
$dbpass='password';

  try {

    $db  = new PDO("mysql:host=".$dbhost.";dbname=".$dbname,$dbuser,$dbpass);
  } catch (PDOException $e) {
    throw new PDOException("Error  : " .$e->getMessage());
echo "error";
  }
?>

