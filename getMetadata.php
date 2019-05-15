<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include ("config.php");
include ("pdo_connect.php");
include ("functions.php");

extract ($_REQUEST);
function getImage($record) {
    $q = "SELECT image_file FROM av2 WHERE bib_record = ?";
    $db = ConnectPDO();
    $params = array($record);
    $stmt = $db->prepare($q);
    $stmt->execute($params);
    $myrow = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($myrow);
    if ($image_file) {
        return ($image_file);
    }
} //end function getImage

if ($record) {
  $lines .= "<div class=\"hide_metadata\" id=\"hide_$record\"><a>X</a></div>\n";
  $db = ConnectPDO();
  $q = "SELECT * FROM av2 WHERE av2.bib_record = ?";
  $params = array($record);
  $stmt = $db->prepare($q);
  $stmt->execute($params);

  $myrow = $stmt->fetch(PDO::FETCH_ASSOC);
  extract($myrow);
  list ($title, $details) = SplitTitle($title);

if ($call) { 
if (! preg_match("/http/",$call)) { //replace slashes in non-URL call numbers
$call = str_replace ("/","+",$call); // eliminate slashes, make spaces
$status = getEzraStatus($record);

    }
    //old:  $ezra_url = "http://ezra.wittenberg.edu/search/f?SEARCH=$call";
    $ezra_url = substr("http://ezra.wittenberg.edu/record=$record",0,-1); // chop off check digit
    //    $call_lines = "<div class=\"call\"><a href=\"$ezra_url\">$call: $status</a></div>\n";
    $call_lines = "<div class=\"call\">$status</div>\n";
  } //end if call

  $image_file = getImage($record);
  if ($image_file) 
    $lines .= "<div class=\"cover\"><a href=\"$ezra_url\"><img src=\"/lib/find/av/covers/$image_file\"></a></div> <div class=\"record_metadata\">\n";
  $lines .= "<div class=\"title\">$title</div>\n";
  $lines .= $call_lines;
  $details = preg_replace ("/ {3,}/", "</div><div class=\"detail_line\">",$details); // 3+ spaces -> break
  $details = str_replace  (";", "</div><div class=\"detail_line\">", $details);
  $lines .= "<div class=\"details\"><div class=\"detail_line\">$details</div></div>\n";
  if (preg_match("/\S/",$summary)) { $lines .= "<div class=\"summary\">Summary: $summary</div>\n"; }
  $lines .= "<br>[<a href=\"$ezra_url\">more info</a>]\n";
  $lines .= "</div>\n"; // end class=record metadata
  print "<div class=\"metadata_return\"><div class=\"prop_cover\"></div>$lines<div class=\"clear\"></div>";
}
?>
