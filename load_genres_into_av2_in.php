<?php
include ("genre_scripts.php");
include ("config.php");
include ("pdo_connect.php");

$imdb_to_lc = array("Action" => "",
		    "Adventure" => "Adventure",
		    "Animation" => "Animated",
		    "Biography" => "Biographical",
		    "Comedy" => "Comedy",
		    "Crime" => "Crime",
		    "Documentary" => "Documentary",
		    "Drama" => "",
		    "Family" => "",
		    "Fantasy" => "Fantasy",
		    "Film-Noir" => "Film noir",
		    "History" => "Historical",
		    "Horror" => "Horror",
		    "Music" => "Music",
		    "Musical" => "Musical",
		    "Mystery" => "Detective and mystery",
		    "Romance" => "Romance",
		    "Sci-Fi" => "Science fiction",
		    "Short" => "Short",
		    "Sport" => "Sports",
		    "Thriller" => "Thriller",
		    "War" => "War",
		    "Western" => "Western"
		    );

$db = ConnectPDO();
$q = "SELECT * FROM av_genre_browse WHERE (imdb_genres IS NOT NULL) or (lc_genres IS NOT NULL)";
print ($q);
$stmt = $db->query($q);

while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
  extract($myrow);
  foreach ($imdb_to_lc as $imdb => $lc) {
    if (strlen($lc) > 0)
      $imdb_genres = str_replace("$imdb","$lc",$imdb_genres);
  } 

  if (strlen($lc_genres) > 0) 
    $temp = preg_split ("/ \| /", $lc_genres);
  else 
    $temp = array();
  if (strlen($imdb_genres) > 0)
    $temp2 = preg_split ("/ \| /", $imdb_genres);
  else 
    $temp2 = array();
  $genres = array_unique(array_merge($temp, $temp2));
  $combined_genres = addslashes(join (" | ", $genres));
  if ($image_file != "") { 
    $image_file = addslashes($image_file);
    $add = ", image_file='$image_file'"; 
  }
  else {$add = ""; }
  $q1 = "UPDATE av2_in set combined_genres = '$combined_genres' $add WHERE bib_record='$record_id'";
  //  print "<br>$q1;";
  if (! $db->query($q1)) { print "<br>FAILED: $q1"; }
} //end while

$q = "UPDATE av2_in SET combined_genres = 'Uncategorized' WHERE lcsh like '%feature film%' and combined_genres = ''";
if ($db->query($q)) { 
  print "<p>Added 'Uncategorized' genre to genreless Feature films</p>\n";
}
else { print "<p>FAILED to add 'Uncategorized' genre to genreless Feature films</p>\n"; }


