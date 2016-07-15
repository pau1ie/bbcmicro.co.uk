<?php
  try {

    $db  = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
  } catch (PDOException $e) {
    throw new PDOException("Error  : " .$e->getMessage());
echo "error";
  }
?>

