<?php
  try {

    $dbh  = new PDO("mysql:host=".ADMIN_DB_HOST.";dbname=".ADMIN_DB_NAME,ADMIN_DB_USER,ADMIN_DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
  } catch (PDOException $e) {
    throw new PDOException("Error  : " .$e->getMessage());
echo "error";
  }
?>

