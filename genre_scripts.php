<?php

function LCSH_to_genres($lcsh) {
  // breaks up the semi-colon-delimited EZRA-lcsh string
  // extracts all "_____ films" as genres
  // and adds an "Anime" genre if the "Anime Club" local heading appears
  $genres = array();
  $lcshs = preg_split ("/;/",$lcsh);
  foreach ($lcshs as $lc) {
    if (preg_match ("/(.+) films/",$lc,$m)) {
      $g = $m[1];
      if ($g == "Foreign langauge") { $g = "Foreign"; } 
      if (! preg_match("/Feature/",$g))
	array_push ($genres, $g);
    } //end if lc genre
  } //end foreach lcsh in list
  if (preg_match("/Anime Club/i",$lcsh)) 
    array_push($genres, "Anime");
  if (preg_match("/Action and adventure/",$lcsh)) {
    array_push($genres, "Action");
    array_push($genres, "Adventure");
  } //end if action and adventure

  $genre_string = "";
  
  if (sizeof($genres) > 0) {
    $genre_string = join (" | ", $genres);
  } //end if there's anything here
  
  return ($genre_string);
}

function SplitTitle ($title) {
  $title = str_replace("\"", "",$title);
  $title = preg_replace("/ \[videorecording\.*\]/i","",$title);
  if (preg_match("/(.?) *\(Motion Picture[\)]*\); *\\1(.*)/i", $title,$m))
    $title = $m[1] .$m[2];
  $return = preg_split ("/ \/ /", $title, 2); // list ($title, $details)
  return ($return);
} //end function SplitTitle
?>
