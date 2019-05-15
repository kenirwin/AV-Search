<!DOCTYPE html> 
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
   <head>
<link rel="stylesheet" type="text/css" href="/scripts/ProStyles.css" />
<link rel="stylesheet" type="text/css" href="/screens/styles.css" />
<script type="text/javascript" src="/scripts/elcontent.js"></script>
<script type="text/javascript" src="/scripts/common.js"></script>
      <meta charset="UTF-8">
      <title>Audio-Visual Search of Library Materials</title>
      <meta name="viewport" content="width=device-width,user-scalable=yes">
      <link rel="shortcut icon" href="https://www.wittenberg.edu/sites/default/files/apple-icon-180x180_0.png" type="image/png" />
   </head>
   <body>
      <div id="header">
         <div class="logo">
            <a href="https://www.wittenberg.edu"><img alt="Wittenberg Logo" src=
    "http://ezra.wittenberg.edu/screens/wittlogo.png" width="200"></a>
         </div>
         <div class="main-links">
            <ul>
               <li><a href="http://www.wittenberg.edu/lib">Library Website</a></li>
               <li><a href="/search~">Ezra Main Page</a></li>
            </ul>
         </div>
      </div>

<script type="text/javascript"
         src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js">
</script>
</head>
<body>

<?php
     /*
error_reporting(E_ALL);
ini_set('display_errors', 1);
     */


// instructions for updating the contents of this database:
// http://www6.wittenberg.edu/lib/test/av/readme.txt


$this_file = $_SERVER['PHP_SELF'];
extract($_REQUEST);
//include("/docs/lib/include/scripts.php");
include('config.php');
include('pdo_connect.php');
include('functions.php'); // av search/browse functions

?>
<link rel=StyleSheet href="/lib/ezra.css" type="text/css">
<link rel=StyleSheet href="av_styles.css" type="text/css">
<?php 
    include ("/docs/lib/include/ibox/headers.html");
?>
<script src="/lib/include/css_browser_selector.js" type="text/javascript"></script>

<script type="text/javascript">
$(document).ready(function() { 
    $('.show').click(function() {
	bib = this.id;
	bib = bib.substr(5);
	$('#'+bib).toggle();
	url = "/lib/include/getStatus.php?format=block&bib="+bib;
	status = $('#status_'+bib).text();
	if (status.length == 0) {
	  $('#status_'+bib).load(url);
	}
      });

    var bindHide = function () {
      $('.hide_metadata').click(function() {
	  bib = this.id;
	  bib = bib.substr(5);
	  $('#'+bib).hide();
	});
    }//end bindHide
    bindHide(); // onload, bind behavior

    $('.get_record').click(function() {
	getbib = this.id;
	bib = getbib.substr(4);
	url = "./getMetadata.php?record=" + bib;
	div = $('#' + bib).text();
	if (div.length == 0) {
	  $('#' + bib).load(url, function() { bindHide(); 
	      $('.bibItemsEntry a').each(function(index) {
		  var loplength = bib.length - 1;
		  var biblop = bib.substr(0,loplength);
		  var temp = 'http://ezra.wittenberg.edu/record=' + biblop + '~S0';
		  $(this).attr('href', temp);
		});
	    });
	}
	$('#'+bib).show();
      });

<?php
if ($_REQUEST['expand_all'] == "yes") {
?>
    $('.full_av_record').each(function() {
	bib = this.id;
	url = "/lib/include/getStatus.php?format=block&bib="+bib;
	status = $('#status_'+bib).text();
	if (status.length == 0) {
	  $('#status_'+bib).load(url);
	}
      });
<?php
 }
?>




  });

</script>


<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<meta http-equiv="expires" content="-1">
<meta http-equiv="pragma" content="no-cache">
<meta NAME="AUTHOR" CONTENT="Wittenberg University Library.">
<meta name="robots" content="all">
<meta NAME="DESCRIPTION" CONTENT="A searchable databases of audio-visual materials held at Thomas Library, Wittenberg University.">
</head>

<?php 
if (! $mode == "browse") { 
  $onLoad = " onLoad=\"document.SearchForm.terms.focus()\""; 
}
?>

<body <?php print $onLoad; ?>>

<h1>Audio-Visual Search</h1>

<?php
if ($mode == "search") { 
  include ("full_search_box.html");
  include("feature_film_ad_thin.html");
  //  print ($_SERVER[REQUEST_URI]);
  DoSearch ($_REQUEST, $_SERVER['REQUEST_URI']);
} //end if search submitted 

elseif ($mode == "browse") { 
  if (preg_match ("/^;+$/",$genre)) { $genre = ""; }
  if ($genre)
    GetGenre($genre);
  elseif ($show == "all")
    GetGenre("","all");
  else 
    DisplayMainPage();
} //end else if browsing

else { //display main page
  DisplayMainPage();
} //end if displaying main page

function DisplayMainPage () { 
  include ("full_search_box.html");
  //  ShowGenres();
  print "<p align=center>";
  include ("feature_film_banner.html");
  print "</p>";
}

?>

<?php

//Breadcrumb("$_SERVER[SCRIPT_NAME]");
print "<div class=\"clear_all\">\n";
//include ("/docs/lib/include/ezra_bottom.html");
?>
