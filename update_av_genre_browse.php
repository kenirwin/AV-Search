<?php
/*
  Updates the lib.av_genre_browse table with info from new EZRA records:
  * updates old LCSH if there have been changes
  * adds new av_genre_browse entries if there are new feature/documentary films
  * does nothing to records where nothing has changed
  * makes a note of date of last update (useful in seeing what's really new)
*/

include ("genre_scripts.php");

include ("config.php");
include ("pdo_connect.php");
$db = ConnectPDO();

/*
 *  Part 1: Lookup existing av_g_b data so we can compare
 */

$q = "SELECT * FROM av_genre_browse";
$stmt = $db->query($q);

while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
  extract ($myrow);
  $old_data[$record_id] = $myrow; //store an array under the record id #
}

/*
 * Part 2: get a list of records of interest from av2_in
 */

$q = "SELECT * FROM av2_in WHERE (lcsh LIKE '%feature films%' OR lcsh LIKE '%documentary films%' OR lcsh LIKE '%anime club') order by bib_record";
$stmt = $db->query($q);

$now = date("Y-m-d");

$no_imdb_genres = $no_image_file = ""; //BLANK PLACEHOLDERS

while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
  extract($myrow);
  if ($old_data[$bib_record] ) { // if old entry exists
    $now_lc_genres = LCSH_to_genres($lcsh);
    $old = $old_data[$bib_record];
    if ($now_lc_genres == $old['lc_genres']) {
      // do nothing
    } //end if no change
    else {
      $now_lc_genres = addslashes($now_lc_genres);
      $lcsh = addslashes($lcsh);
      $q = "UPDATE av_genre_browse SET subject = '$lcsh', lc_genres = '$now_lc_genres', last_updated = '$now' WHERE record_id = '$bib_record'";
      if (! $db->query($q)) {
          print "<li>FAILED: $q\n";
      }
      else {
          print $q;
      }
    }

  } //end if old entry exists

  else { // if this is a new entry 
    $lc_genres = addslashes(LCSH_to_genres($lcsh));
    $title = addslashes($title);
    $lcsh = addslashes($lcsh);
    $pub = addslashes($pub);
    //    print "<br>$lc_genres\n";
    $q = "INSERT INTO av_genre_browse VALUES ('$bib_record','$title','$lcsh','$pub','$lc_genres','$no_imdb_genres','$no_image_file','$now')";
    if (! $db->query($q))
      print "<li>FAILED: $q\n";
  }
  
  
} //while looking through av2_in for new/incomplete genre info

print PHP_EOL."the end".PHP_EOL;
?>
