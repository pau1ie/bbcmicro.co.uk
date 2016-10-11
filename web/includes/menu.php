<?php
function make_menu_bar($active_tab,$lprefix='') {
	echo "<div id=\"navbar\" class=\"collapse navbar-collapse\">\n";
	echo "<ul class=\"nav navbar-nav\">\n";
	foreach (array	(	"Games"=>$lprefix."index.php",
				"About"=>$lprefix."about.php",
				"Links"=>$lprefix."links.php",
				"Contact"=>$lprefix."contact.php",
			) as $name=>$target) {
		$class=($active_tab==$name)?" class=\"active\"":'';
		echo "<li$class><a href=\"$target\">$name</a></li>\n";
	}
	echo "</ul>\n";
	echo "</div><!-- /.nav-collapse -->\n";
}
?>
