<?php
include ("genre_scripts.php");
include ("config.php");
include ("pdo_connect.php");

print('
<style>
.details {
  font-size: 70%;
  background-color: lightgrey;
  border-bottom: 2px solid black
}

.fail { background-color: red }
.minor { 
 font-size: 78%;
 color: grey;
}
.minor a:visited { 
 color: grey;
}
</style>');



if (array_key_exists('add_genres',$_REQUEST)) {
  foreach ($_POST as $k=>$v) {
    if ((preg_match("/^b\d+/",$k)) && strlen($v) > 0) {
      $tempstring = "image_file_". $k;
      if ($_POST[$tempstring]) {
	if (! preg_match("/\.(jpg|gif|jpeg|png)$/",$_POST[$tempstring]))
	  $_POST[$tempstring] .= ".jpg";
	$_POST[$tempstring] = str_replace(" .jpg",".jpg",$_POST[$tempstring]);
	$and = ", image_file='$_POST[$tempstring]'";
      }
      else 
	$and = "";
      $now = date ("Y-m-d");
      $db = ConnectPDO();
      $q = "UPDATE av_genre_browse SET imdb_genres='$v' $and, last_updated='$now' WHERE record_id ='$k'";
      //      print "<br>$q\n";
      if ($db->query($q)) { print "Added: $k => $v<br>\n"; }
      else { print "<p class=fail>FAILED to add $k => $v<br>$q</p>\n"; }
    }
      
  }
}

ShowUnassigned();

function ShowUnassigned() {
  $q = "SELECT * FROM av_genre_browse WHERE subject like '%feature film%' and (imdb_genres IS NULL or imdb_genres = \"\") order by last_updated DESC";
  $db = ConnectPDO();
  $stmt = $db->query($q);
  while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($myrow);
    list ($title, $details) = SplitTitle($title);
    $url = "http://ezra.wittenberg.edu/record=". substr($record_id,0,8);
    $rows .= "<tr class=title><th><a href=\"http://www.imdb.com/find?q=$title\" target=\"new\">$title</a> <span class=\"minor\">[<a href=\"$url\" target=\"ezra\">ezra</a>][last updated: $last_updated]</span></th> <td><input type=text name=\"$record_id\" size=50 value=\"\"/></td></tr> <tr><td class=details>$details<br>$publisher</td> <td class=details>Image file: <input type=text name=image_file_".$record_id." size=30 value=\"\"></td></tr>\n";
  } //end while
  $rows .= "<tr><th colspan=2><input type=submit name=\"add_genres\"></tr>\n";
  print "<table><form method=POST>$rows</table>\n";
}
?>
