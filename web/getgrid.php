<?php
// Returns the appropriate page on the main grid, with the paginator.

require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/makegrid.php';
require 'includes/parsevars.php';

$state=getstate();

grid($state);

?>
