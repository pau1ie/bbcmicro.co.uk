<?php

require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/menu.php';
require 'includes/makegrid.php';
require 'header.php';
require 'includes/parsevars.php';

$state=getstate();


htmlhead();
nav();
containstart($state);

echo '    <div id="maingrid">'."\n";
grid($state);
echo '    </div>';
sidebar($state);
containend();
htmlfoot();

