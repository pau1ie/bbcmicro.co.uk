# bbcmicro.co.uk
Source for http://bbcmicro.co.uk - to showcase vintage games for the BBC Micro Computer

To install, first install the standard mysql, php, apache stack.
Create a database, and a user to access it.
Alter config.php to contain the details of the database.
Run db/db.sql into the database, and the site should work.

Only a few disc images are included. Place these in gameimg/discs.
Please note that the game disc images have their own licenses. 
Please check each disc for its license, or retrosoftware.co.uk where most
images came from.

Development is being coordinated via the stairway to hell forums at:

http://stardot.org.uk/forums/viewtopic.php?f=51&t=11165

To play games on the website, download jsbeeb, and install it in its own directory.

Copy play.php into the jsbeeb directory, and alter config.php to point to it as the
player. This will call jsbeeb in a window that looks like the rest of the website.
