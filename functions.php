<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$suppress_genres = array ("Action and adventure",
			  "Feature",
			  "Frankenstein",
			  "James Bond",
			  "Rock",
			  "Vampire",
			  "Zorro"
			  );





function DoSearch ($request, $RequestURI) {
  //  print_rr($request);
  extract($request);
  $params = array();
  if ($show_only == "pub_perf") {
    $searchstring = "public_perf = ?";
    array_push($params,'Y');
  }
  elseif ($show_only == "pub_perf_features") {
    $searchstring = "public_perf = ? and combined_genres != ?";
    array_push($params,'Y');
    array_push($params,'');
  }
  if ($terms) {
    if ($bool) {} else {$bool="and";}
    if ($bool == "phrase") 
      { $search = array($terms); }
    else $search = preg_split("/[ ]+/", $terms);
    if (($field == "any")||($field=="")) { $fields = array ("title","author","lcsh","note"); }
    else { $fields = array ("$field"); }
    //  $fields = array ("title","author");
    
    
    $size = sizeof($fields);
    foreach ($search as $item) { 
      for ($i=0; $i<$size; $i++) {
	$temp[$i] = "$fields[$i] like ?";
    array_push($params,"%$item%");
      } #End for each field
			$temp_str = join (" or ", $temp);
      $disp_terms = join (" $bool ", $search);
      $sub_clause[$j] = "($temp_str)";
      $j++;
    } #end foreach search term
	$searchstring = join (" $bool ", $sub_clause);
    //    echo "Prelimit: $searchstring<BR>\n";
  } #end if $terms
    
    
    if ($exclude) {
      $j=0;
      $exclude_a = split ("[ ]+", $exclude);
      foreach ($exclude_a as $item) { 
	for ($i=0; $i<$size; $i++) {
	  $temp[$i] = "$fields[$i] not like ?";
      array_push($params, "%$item%");
	} #end for each field
    $temp_str2 = join (" and ", $temp);
	$disp_ex = join (" or ", $exclude_a);
	$sub_clause2[$j] = "($temp_str2)";
	$j++;
      } #end foreach search term
	  $excludestring = join (" and ", $sub_clause2);
      $searchstring .= " and $excludestring";
    } #end if exclusions
      
      
      // print "postlimit: $searchstring<BR>\n";
      
      
      // Formats
      $formats = array();
      if (array_key_exists('format',$request)) {
    foreach ($format as $item) { 
      
      if ($item == "LP") { array_push ($formats, "LP","SR","EP","KS","CRL2","we","ft","ex","FS","ARC","Angel"); }
      if ($item == "CD") { array_push ($formats, "CD","ZCDY"); }
      if ($item == "CT") { array_push ($formats, "CT","ZCTY"); } 
      if (($item == "DVD")||($item == "MP")) { array_push ($formats, "$item"); }
      if ($item == "VHS") { array_push ($formats,"VHS","VT");}
      if ($item == "Real" || preg_match('/\[electronic resource\]/',$title)) { array_push ($formats, "http");}
    } # end foreach format 
	} #end if format specified
			   
			   for ($i=0; $i<sizeof($formats); $i++) { 
			     // add a space after call-number start except for http calls
			     if ($formats[$i] != "http") { $formats[$i] .= " "; } 
                 array_push($params, "%$formats[$i]%");
			     $formats[$i] = "`call` like ?"; 
			   } #end for formats
			     
			     $temp = join (" or ", $formats);
                 $format_str = "($temp)";
  // echo "$format_str<BR>\n";
  
  if (sizeof($formats)>0) { $searchstring = "($searchstring) and ($format_str)";}
  
  //limit to public performance rights
  if ($ppr == "on") { 
      $searchstring = "($searchstring and public_perf = ?)"; 
      array_push($$params,'Y');
  }
  
  
  $searchstring .= " order by sort";
  
  //  if ($terms) {
  
  if ($searchstring) {
      //      print $searchstring.'<br>'.PHP_EOL;
      /*
      print(substr_count($searchstring,'?')).PHP_EOL;
      print_r(sizeof($params)).PHP_EOL;
      print_r ($params);
      */
      $db = ConnectPDO();
      $stmt = $db->prepare('SELECT uniq FROM av2 where '.$searchstring);
      $stmt->execute($params);
      $num_rows = $stmt->rowCount();
    
    
    //    print $query_c;
    //print ($RequestURI);

    if ($expand_all == "yes") { 
      $default_display="block";
      $RequestURI = preg_replace ("/expand_all=yes/", "expand_all=no",$RequestURI);
      
      $expansion_toggle = " | <a href=\"$RequestURI\">Show list of titles, not full records</a>\n";
    }
      else { 
	$default_display = "none"; 
	$RequestURI = preg_replace ("/expand_all=no/", "", $RequestURI);
	$RequestURI .= "&expand_all=yes";
	$expansion_toggle =" | <a href=\"$RequestURI\">Show full records for all results</a>";
      } //end else if not showing all records

    $stmt = $db->prepare('SELECT * FROM av2 where '.$searchstring);
    $stmt->execute($params);
    $count = $stmt->rowCount();

    $results_indicator = "<h3 class=\"results_indicator\">$count results $expansion_toggle</h3>\n";
    print "<div id=\"search_results_area\">\n";
    print ($results_indicator);
    print "<ol id=\"search_results_list\">\n";
    while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
      extract($myrow);
      
      
      // turn call number into ezra link
      //deprecated     $ezra = "http://ezra.wittenberg.edu/search/f?SEARCH=";
      
      //  replace duplicated call numbers(VHS 5;VHS 5)
      $call = ( preg_replace ("/^(.*)\;(\\1)/", "\\1", $call));
      
      $calls = preg_split ("/;/", $call);
      foreach ($calls as $item) {
	$call_disp = $item;
	$item = str_replace("/"," ",$item);
	$item = preg_replace ("/\s+/", "+", $item);
	$permlink = substr("http://ezra.wittenberg.edu/record=$bib_record",0,-1); // chop off check digit
	if (preg_match("/http/",$call)) { $check = "<strong>Connect to online video: $call</strong>"; } 
	else { 

	  //	  $link = "<a href=\"$permlink\" target=ezra onClick='catWin = window.open(\"\",\"ezra\",\"menubar,scrollbars,toolbar,resizable,width=550,height=400\"); catWin.focus()'>View in EZRA\"></a> \n";
	  $check = "<p id=\"record_$bib_record\"><span class=\"status\" id=\"status_$bib_record\"></span> $link</p>\n";
	} //end else 
      }
      // finished altering call number



      
      // pretty up subject headings
      $lcsh_a = preg_split ("/;/", $lcsh);
      $lcsh_str = "";
      foreach ($lcsh_a as $item) {
	$temp = $item;
	$temp = preg_replace ("/ +|-+/", "+", $temp);
	$lcsh_str .= "<a href=\"$this_file?field=lcsh&terms=$temp\">$item</a><BR>\n";
      }
      
      // pretty up the note,pub,extent field
      $note = str_replace (";", "<BR>\n", $note);
      $pub = str_replace (";", "<BR>\n", $pub);
      $extent = str_replace (";", "<BR>\n", $extent);
      
      // pretty up contributor list
      $author = str_replace (";", "<BR>\n", $author);
      $author = str_replace ("prf", "(performer)", $author);
      $author = str_replace ("drt", "(director)", $author);
      $author = str_replace ("nrt", "(narrator)", $author);
      
      //	  print "$title<BR>$author<BR><a href=$permlink>$callstring</a><P> \n";
      
      if (preg_match("/Feature film/",$lcsh))
	$icon = "<span class=\"margin_icon_feature_film\"><img src=\"ff_icon.png\" width=45 title=\"Feature Film\"></span>\n";
      else 
	$icon = "";

      print "<li><div class=\"search_results_prop\"></div><a name=\"jumpto$bib_record\"></a><a href=\"#jumpto$bib_record\" class=\"show\" id=\"show_$bib_record\">$icon$title</a></li>\n";
	print "<div id=\"$bib_record\" style=\"display:$default_display\" class=\"full_av_record\"><table>\n";
      print  "<div class=\"hide_metadata\" id=\"hide_$bib_record\">X</div>\n";
      if (strlen($image_file) > 0 && is_readable("/docs/lib/find/av/covers/$image_file")) { 
	$img = "<img src=\"/lib/find/av/covers/$image_file\" class=\"search_cover\">\n";
      }
      else { $img = ""; }
      print "<tr><th>Title</th> <td>$img$title</td></tr>\n";
      print "<tr><th>Availability</th> <td>$check</td></tr>\n";
      print "<tr><th>Contributors</th> <td>$author</td></tr>\n";
      print "<tr><th>Published</th> <td>$pub></tr>\n";
      print "<tr><th>Contents</th> <td>$extent</tr>\n";
      print "<tr><th>Notes</th> <td>$note</td></tr>\n";
      print "<tr><th>Subject Headings</th> <td>$lcsh_str</td></tr>\n";

      if ($public_perf == "Y") { $ppr = "Yes"; } else { $ppr = "No"; }
      print "<tr><th>Public Peformance Rights?</th><td>$ppr</td></tr>\n";
      print "</table>\n</div>\n\n";
    } //while fetching results

    print "</ol>\n";
    if ($count > 0) { print "$results_indicator\n"; }
    print "</div><!-- id=search_results-area -->\n";
  } //end if terms
} //end function DoSearch




function GetGenre ($genre,$show="") {
  global $format, $suppress_genres;
  $_REQUEST['genre'] = stripslashes($_REQUEST['genre']);

  if ($show == "all" || $_REQUEST['show'] == "all") { 
    $query_prefix = "show=all&";
  }

  if ($format) { $query_prefix .= "format=$format"."&"; }

  if (! $query_prefix) { $query_prefix = ""; }
  if ($format) { 
    if ($format == "Online Video") { $format = "<a href"; }
    $add_format_query = " AND `call` LIKE '$format%'"; 
    if ($format == "<a href") { $format ="Online Video";}
    $uri_string = urldecode($_SERVER['REQUEST_URI']);
    $format_limit_elim = preg_replace("/\&*format=$format\&*/","&",$uri_string);
    $format_limit_elim = "<a href=\"$format_limit_elim\" class=\"eliminate_facet\">x</a>\n";
    $echo_format_limit = ", limit to $format"."s$format_limit_elim";
  }

//  print "<h1>G1: $_REQUEST[genre]</h1>\n";
  $genre = preg_replace ("/ +/", " ",$genre); //collapse all whitespace to one
  $genre = preg_replace ("/^;+/", "",$genre); //delete all leading whitesp
  $genre = preg_replace ("/;+$/", "",$genre); //delete all trailing whitesp
  $_REQUEST['genre'] = preg_replace ("/^;+/", "",$genre); //delete all leading whitesp
  $_REQUEST['genre'] = preg_replace ("/;+$/", "",$genre); //delete all trailing whitesp
  //  print "<h1>G2: $_REQUEST[genre]</h1>\n";


  $genres = preg_split ("/;/",$genre);
  //stash terms in array + convert into boolean search string
  $query_array = $echo_array = array();
  foreach ($genres as $g) {
    array_push ($query_array, "(combined_genres LIKE '%$g %' OR combined_genres LIKE '%$g')");
    // note: that query string is extra-complicated so it looks for a space
    // or an end-of-line after the search term so that 
    // Music doesn't find Music*, eg Musical
    $print_g = stripslashes($g);
    if (preg_match("/\S/", $print_g)) // only if there's anything to display
      array_push ($echo_array, "$print_g <a href=\"index.php?$query_prefix"."mode=browse&genre=".preg_replace("/$g/","",$_REQUEST['genre'])."\" class=\"eliminate_facet\" title=\"Remove $g from Search\">x</a>");
  } //end foreach queried genre
  $echo_string = join (" + ", $echo_array);
  $query_string = join (" AND ", $query_array);

  if ($show == "all") { 
    $query_string = "lcsh LIKE '%feature_film%'"; 
  }

  $q = "SELECT * from av2 WHERE $query_string $add_format_query order by sort";
  //     print $q;
  $db = ConnectPDO();
  $stmt = $db->query($q);
  print "<a href=\"./\"><img src=\"ff_icon.png\" style=\"float:left; margin-right: 1.5em\"></a>\n";
  print "<h2>You're viewing the Feature Film Collection<br>";
  if (preg_match("/\S/","$echo_string$echo_format_limit")) 
    print "Browsing for: $echo_string$echo_format_limit</h2>\n";
  else print "</h2>\n";
  $results_indicator = $stmt->rowCount()." results";
  include ("search_strip.html");
  print "<br clear=both>\n";
  while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($myrow);
    $temp = preg_split ("/ \| /", $combined_genres);
    foreach ($temp as $addedgenre) {
      $counts[$addedgenre]++;
    } //end foreach
    list ($title, $details) = SplitTitle($title);
    $rows .= "<li><a class=\"get_record\" id=\"get_$bib_record\">$title</a><div id=\"$bib_record\"></div></li>\n";
    //    $rows .= GetURLContents("http://www6.wittenberg.edu/lib/research/av/getMetadata.php?record=$bib_record");
    //    $rows .= "</div></li>\n";
    $format = "";
    if (preg_match("/href/",$call)) { //if online video
      $format = "Online Video"; 
      $format_counts[$format]++;
    } //end if online video
    elseif (preg_match("/([a-zA-Z]+)/",$call,$m)) {
      $format = $m[1];
      $format_counts[$format]++;
    }//end if finding format
    if ($image_file) { 
      if (is_readable("/lib/find/av/covers/$image_file") &! $printed_image[$image_file]) {
	//$covers .= "<a name=\"$title\"><img src=\"covers/$image_file\" height=120/></a>\n";
	  $printed_image[$image_file] = "yes"; // don't print twice
      }
    }
  } //end while myrow
  
  arsort($format_counts);
  foreach ($format_counts as $f=>$c)
    $format_facets .= "<li><a href=\"index.php?$query_prefix"."mode=browse&genre=".addslashes($_REQUEST['genre'])."&format=$f\">& $f <span class=\"genre_count\">($c)</span></a></li>\n";

  arsort($counts);
  foreach ($counts as $g=>$c) {
    //    $_REQUEST[genre] = stripslashes($_REQUEST[genre]);
    if (! preg_match("/$g/",$_REQUEST['genre'])) // don't show genres already in search
      if (! in_array($g, $suppress_genres)) //skip suppressed genres
	$facets .= "<li><a href=\"$_SERVER[PHP_SELF]?$query_prefix"."mode=browse&genre=".addslashes($_REQUEST['genre']).";$g\">& $g <span class=\"genre_count\">($c)</span></a></li>\n";
  }
  print "<div id=\"facets\"><h2>Narrow Results Within<br>$echo_string:</h2>";
  if (sizeof($format_counts)>1) 
    print "<h3>Formats</h3><ul>$format_facets</ul>\n";
  print "<h3>Genres</h3><ul>$facets</ul></div>\n";
  //  if ($covers)    print "<div id=\"covers\">$covers</div>\n";
  print "<div id=\"results\"><h2>$results_indicator</h2><ol>$rows</ol></div>\n";

  
} //end function GetGenre

function ShowGenres () {
  global $sort, $suppress_genres;
  $field = "combined_genres";
  $table = "av2";

  $db = ConnectPDO();
    // first get total number of feature films
  $q = "SELECT count(*) FROM $table WHERE lcsh like '%feature films%'";
  $stmt = $db->query($q);
  $myrow= $stmt->fetch(PDO::FETCH_NUM);
  $total_count = $myrow[0];

  // now get counts for each genre
  $q = "SELECT $field FROM $table WHERE $field IS NOT NULL and $field != ''";
  $stmt = $db->query($q);

  while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($myrow);
    $temp = preg_split ("/ +\| +/", $$field);
    foreach ($temp as $genre) {
      $genre = chop($genre);
      if (! in_array($genre, $suppress_genres))
	$counts[$genre]++;
      if (preg_match("/Anime Club/i",$lcsh))
	$counts['Anime']++;
    } //end foreach
  } //end while
  if ($sort == "count")
    arsort($counts);
  else //sort by genre name unless otherwise specified
    ksort($counts);
  //  $list_size = sizeof($counts);
  //  print_r($counts);
  foreach ($counts as $g => $c) {
    $g_url = addslashes($g);
    if (strlen($g)>0) //skip blank/unnamed
      $rows .= "<li><a <a href=\"index.php?$query_prefix"."mode=browse&genre=$g_url\">$g</a> ($c)</li>\n";
  }
  print "<div id=\"genre_list_header\">\n";
  print "<h2>Feature Film Genres</h2>";
  print "<p><a href=\"index.php?mode=browse&show=all\">View All $total_count Feature Films</a></p><br>\n";
  /*
  if ($sort == "count") { print "<p>Sorted by number of entries | <a href=\"?sort=genre\">Sort by genre name</a></p>\n"; }
  else { print "<p>Sorted by genre name | <a href=\"?sort=count\">Sort by number of entries</a></p>\n"; }
  */
  print "</div>\n";

  print "<div id=\"genre_list_div\">\n";
  print "<ul id=\"genre_list\">$rows</ul>\n";
  print "</div><br clear=left>\n"; 
} //end function ShowGenres



function LCSH_to_genres($lcsh) {
  // breaks up the semi-colon-delimited EZRA-lcsh string
  // extracts all "_____ films" as genres
  // and adds an "Anime" genre if the "Anime Club" local heading appears

  $genres = array();
  $lcshs = preg_split ("/;/",$lcsh);
  foreach ($lcshs as $lc) {
    if (preg_match ("/(.+) films/",$lc,$m)) {
      $g = $m[1];
      if (! preg_match("/Feature/",$g))
	array_push ($genres, $g);
    } //end if lc genre
  } //end foreach lcsh in list
  if (preg_match("/Anime Club/i",$lcsh)) 
    array_push($genres, "Anime");

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


function getEzraStatus($record) {
    if (preg_match('/(b\d{7})/',$record,$m)) {
        $record = $m[1]; //only b+7 digits -- skip anything else
    }
    
    $url = 'http://ezra.wittenberg.edu/record='.$record;
    $html = file_get_contents($url);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    /* select the part of the record we want */
    $result = $xpath->query("//table[@id='bib_items']");
    if ($result->length > 0) {
        $bib_info = $dom->saveHTML($result->item(0));
        return $bib_info;
    }
    else {
        return false;
    }
}

function getEzraBibByRecord($record) {
    if (preg_match('/(b\d{7})/',$record,$m)) {
        $record = $m[1]; //only b+7 digits -- skip anything else
    }
    
    $url = 'http://ezra.wittenberg.edu/record='.$record;
    $html = file_get_contents($url);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    /* select the part of the record we want */
    $result = $xpath->query("//div[@class='bibInfo']");
    $bib_info = $dom->saveHTML($result->item(0));
    
    /* remove parts we don't want from the wanted part */
    $unwanted_xpaths = array(
        "//div[@id='navigationRow_bottom']",
        "//td[@id='bibImage']",
        "//img"
    );
    foreach ($unwanted_xpaths as $unwanted) {
        $result = $xpath->query($unwanted);
        if ($result->length > 0){
            for ($i=0; $i<$result->length; $i++) {
                $elim = $dom->saveHTML($result->item($i));
                $bib_info = str_replace($elim,'',$bib_info);
            }
        }
    }    
    /* print what's left */
    print $bib_info;
} //end getEzraBibByRecord

?>


