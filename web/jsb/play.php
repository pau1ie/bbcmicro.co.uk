<!DOCTYPE html>
<?php
require_once('../includes/menu.php');
$bgcolour = "#333";
if (isset($_GET['bg'])) {
  switch ($_GET['bg']) {
   case "black":
     $bgcolour = "#000;";
     break;
   case "white":
     $bgcolour = "#fff";
     break;
   default:
     $bgcolour = "#333";
 }
}
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../favicon.ico">

    <title>Play</title>

    <!-- Bootstrap core CSS -->
    <link href="../bs/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../bs/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../bs/css/jumbotron.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="jsbeeb.css" type="text/css">
    <script type="text/javascript" src="lib/require.js"></script>
    <script type="text/javascript">
        require(['requirejs-common'], function () {
            require(['main']);
        });
    </script>
    <style>
    body {
        background-color: <?php echo $bgcolour; ?>
    }
    </style>
  </head>

  <body>

 <nav class="navbar navbar-fixed-top navbar-inverse">
  <div class="container">
   <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
     <span class="sr-only">Toggle navigation</span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
    </button>
    <a href="../index.php" class="navbar-brand">Complete BBC Games Archive</a>
   </div>
   <?php make_menu_bar("Games","../"); ?>
  </div><!-- /.container -->
 </nav><!-- /.navbar -->
    <div class="container">

   <div id="cub-monitor">
        <div>            
            <canvas id="screen" width="921" height="682"></canvas>
        </div>
    </div>
    <div id="leds" style="display:none">
        <table>
            <thead>
            <tr>
                <th>cassette<br>motor</th>
                <th>caps<br>lock</th>
                <th>shift<br>lock</th>
                <th>drive<br>0/2</th>
                <th>drive<br>1/3</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div class="red led" id="motorlight"></div>
                </td>
                <td>
                    <div class="red led" id="capslight"></div>
                </td>
                <td>
                    <div class="red led" id="shiftlight"></div>
                </td>
                <td>
                    <div class="yellow led" id="drive0"></div>
                </td>
                <td>
                    <div class="yellow led" id="drive1"></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div id="debug" class="initially-hidden">
        <div class="debug-container">
            <form class="form-inline" role="form" id="goto-mem-addr-form">
                <div class="form-group">
                    <label accesskey="m" class="control-label"><span class="accesskey">M</span>em
                        addr:</label>
                    <input type="text" class="form-control input-sm goto-addr" placeholder="$0000">
                </div>
            </form>
            <div id="memory">
                <div class="template"><span class="dis_addr">0000</span><span
                        class="mem_bytes">11 22 33 44 55 66 77 88</span><span
                        class="mem_asc">ABCDEFG</span></div>
            </div>
        </div>
        <div class="debug-container">
            <form class="form-inline" role="form" id="goto-dis-addr-form">
                <div class="form-group">
                    <label accesskey="a" class="control-label"><span class="accesskey">A</span>ddr:</label>
                    <input type="text" class="form-control input-sm goto-addr" placeholder="$0000">
                </div>
            </form>
            <div id="disassembly">
                <div class="template"><span class="dis_addr">0000</span><span
                        class="instr_bytes">11 22 33</span><span class="instr_asc">ABC</span><span
                        class="disassembly">LDA (&amp;70), X</span></div>
            </div>
        </div>
        <div id="registers">
            <div>
                <span class="flag" id="cpu6502_flag_c">C</span><span class="flag" id="cpu6502_flag_z">Z</span><span
                    class="flag" id="cpu6502_flag_i">I</span><span class="flag" id="cpu6502_flag_d">D</span><span
                    class="flag" id="cpu6502_flag_v">V</span><span class="flag" id="cpu6502_flag_n">N</span>
            </div>
            <div><span class="register">A</span>: <span id="cpu6502_a">00</span></div>
            <div><span class="register">X</span>: <span id="cpu6502_x">00</span></div>
            <div><span class="register">Y</span>: <span id="cpu6502_y">00</span></div>
            <div><span class="register">S</span>: <span id="cpu6502_s">00</span></div>
            <div><span class="register">PC</span>: <span id="cpu6502_pc">0000</span></div>
        </div>
    </div>
    <div id="hardware_debug" class="initially-hidden">
        <div class="via_regs" id="sysvia">
            <h6>System VIA</h6>
            <table>
                <tbody>
                <tr class="template">
                    <th><span class="register"></span>:</th>
                    <td class="value"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="via_regs" id="uservia">
            <h6>User VIA</h6>
            <table>
                <tbody>
                <tr class="template">
                    <th><span class="register"></span>:</th>
                    <td class="value"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="About">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">About</h4>
            </div>
            <div class="modal-body">
			   <p>This is a slimmed down version of Matt Godbolt's awesome BBC emulator JSBeep.</p>
			   <p>This version simply removes the menus so that younger kids do not get distracted.</p>
			   <p>Check out <a href="https://github.com/mattgodbolt/jsbeeb">https://github.com/mattgodbolt/jsbeeb</a> for more info.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="discs">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Load disc image</h4>
            </div>
            <div class="modal-body">
                <ul id="disc-list">
                    <li class="template"><a href="#"><span class="name"></span></a> - <span
                            class="description"></span></li>
                </ul>
                To load a custom disc image, get an SSD file and load it below. Search the web, or check somewhere
                like <a href="http://www.bbcmicrogames.com/GettingStarted.html">here</a> for these. Be aware the
                images are usually stored in a ZIP file, and you'll need to unzip first.
                <div class="disc">
                    <label>Load local SSD file: <input type="file" id="disc_load" accept=".ssd,image/*"></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="tapes">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Load tape image</h4>
            </div>
            <div class="modal-body">
                To load a custom tape image, get a UEF file and load it below.
                <div class="tape">
                    <label>Load local UEF file: <input type="file" id="tape_load" accept=".uef,image/*"></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="loading-dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h4 class="modal-title loading"></h4>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="sth">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Load from <a href="http://www.stairwaytohell.com/">Stairway to Hell</a>
                    archive</h4>

                <div class="loading">Loading catalog from STH archive...</div>
                <div class="filter">
                    <input type="text" placeholder="Filter..." autofocus id="sth-filter">
                </div>
            </div>
            <div class="modal-body">
                <ul id="sth-list">
                    <li class="template"><a href="#"><span class="name"></span></a></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="error-dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">An error occurred - sorry!</h4>

                <div>While <span class="context"></span>:</div>
                <div class="error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="are-you-sure">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Are you sure?</h4>

                <div class="context"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default ays-no" data-dismiss="modal">No</button>
                <button type="button" class="btn ays-yes">Yes</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>

    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="../bs/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="../bs/js/ie10-viewport-bug-workaround.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-80530510-1', 'auto');
  ga('send', 'pageview');

</script>
  </body>
</html>


