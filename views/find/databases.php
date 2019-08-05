<script type="text/javascript"
         src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js">
</script>

<!-- Place this div in your web page where you want your chat widget to appear. -->
<div id="chat-wrapper" style="float:right">
<div class="needs-js">chat loading...</div>
</div>

<?php 
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
require_once '../../vendor/autoload.php';
require_once '../../config.php';
getConfig('lib');
use Wittlib\SearchTable;
use Wittlib\Subjects;

define("DRUPAL_URL", "browse-db");
extract($_REQUEST);
$url_prefix = "https://www6.wittenberg.edu";
$hidden = '';

/*only use this javascript if non-IE browser */
if(isset ($_SERVER['HTTP_USER_AGENT']) &! preg_match('/msie/i',$_SERVER['HTTP_USER_AGENT'])) {
  $ua = $_SERVER['HTTP_USER_AGENT'];
  print "\n<!-- $ua  --> \n";
} // end if non-IE Browser
?>


<script>
$(document).ready(function() {
$.get("https://ipinfo.io", function(response) {
    var ip = response.ip;
    if (ip.startsWith('136.227')) {
        $('#off-campus-note').hide();
    }
}, "jsonp");
});
</script>
<script>
jQuery.expr[':'].iContains = function(a,i,m){
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase())>=0;
};

function HideNonMatch(search) {
    $('#filterable p:not(:iContains('+search+'))').each(function() {
        $(this).hide();
    });

}

function FilterDB () {
    var search = $('#q').val();
    if (search.length == 0) {
        $('#filterable p').each(function() {
            $(this).show();
        });
    }
    else {
        var terms=search.split(" ");
        for (x in terms) {
            HideNonMatch(terms[x]); //(search);
        }
    } //end else if not zero-length search
} //end FilterDB

$(document).ready(function() {
    $('#filter').html('<form method="get" autocomplete="off"><label for="q">Find databases by keyword: </label><input type="text" value="" name="q" id="q" placeholder="Enter database name or keyword" size="50"/></form>');


    /*
      $('#q').click(function() {
          $.ajax({ url: "log-filter.php" });
            }); //end onclick
    */

    var search = $('#q').val();
    $("#q").keyup(function() {
        $("#filterable p").show();
        FilterDB();
    });
    $("#q").focus(function() {
        FilterDB();
    });
});
</script>

<?php

/*******
 * GET BASIC # / Title Associations for later use
 *******/
//$db = new SearchTable();
$db = new SearchTable;
$db->q->table('db_new');
$rows = $db->q->get();
foreach ($rows as $row) {
    $id = $row['ID'];
    $title = $row['title'];
    $db_names[$id] = $title;
}

/******
 * SHOW DETAIL IF REQUESTED
 ******/
if (array_key_exists('info',$_REQUEST)) { // if looking at an individual record in detail
    $info = $_REQUEST['info'];
    $db = new SearchTable();
    $db->q->table('db_new')->where('ID',$info);
    foreach ($db->q->get() as $myrow) {
  extract($myrow);
  $proxy = $requireproxy;
  $proxy_prob = $proxy_problem;
  // if ($requireproxy == "N") { $proxy = "noproxy"; }
  // else { $proxy = ""; }
  if (! $long_desc) {$long_desc = $short_desc; }
  if ($ID) {  
    if ($requireproxy == "N" || $proxy_prohibited == "Y") { $noproxy = "noproxy"; }
    else { $noproxy = ""; }
    if ($url || $route_to_db) {print "<h1><a href=\"$url_prefix/redirect$noproxy.php?$ID\">$title</a><!-- $proxy --></h1>"; }
    else { print "<h1>$title</h1>\n";}
  } // end if ID
  else { print "<h1>$title</h1> <!-- $proxy -->";}
  print "<table width=65%>\n"; 
  print "<tr><th>Description:</th> <td>$long_desc</td></tr>\n";
  print "<tr><th>URL:</th> <td><a href=\"$url_prefix/redirect.php?$ID\">$url</a></td></tr>\n";
  if ($deprecated && (time() > strtotime($deprecated))) {
    $alternatives = "";
    $alt_array = array();
    $alt_array = preg_split ('/;/', $use_new_db);
    foreach ($alt_array as $k => $v)
      $alt_array[$k] = "<a href=\"$url_prefix/redirect.php?$v\">$db_names[$v]</a>";
    $alternatives = join (" or ", $alt_array);
    $ddate= date("F Y", strtotime($deprecated));
    $note = "This database is still available but ceased updating in $ddate.";
    if (strlen($alternatives)>0) { $note .= "Please try $alternatives as a more up-to-date alternative. Length(" . strlen($alternatives). ")"; }
    $note .= "</span>\n";
    print '<tr><th>Warning:</th> <td class="note">'.$note.'</td>'.PHP_EOL;
  } // if deprecated

  if ($dates) { print " <tr><th>Dates of Coverage:</th> <td>$dates</td></tr>\n"; }
  if ($proxy_prohibited == "Y") { 
    print '<tr><th>Notice:</th> <td class="note">This database is only available from on-campus. Our license agreement prevents us from allowing access to off-campus users.</td></tr>';
  } //end if proxy prohibited
  if ($proxy_prob == "Y") {
    print "<tr><th>Warning:</th> <td class=note>Off-campus users may have trouble using this resource due to incompatibility with our proxy server.</td></tr>\n";
  } //end if proxy problem
  print "</table>\n";
  } // foreach myrow=results
} // end if ($info) // if looking at an individual record in detail

/*****
 * DISPLAY list of databases
 *****/


//
// DISPLAY subject pulldown and QUERY for list of dbs
//



else { // if not an individual record
    if (isset($_REQUEST['curr_subj'])) {
        $curr_subj = $_REQUEST['curr_subj'];
        $s = new Subjects();
        $subject = $s->subjectDecode($curr_subj);
    }
    else { $curr_subj = ''; }
    $curr_subject = $curr_subj;
    if (! isset($on_campus) || ($on_campus == false)) {
     print '<div class="note" id="off-campus-note">Note for off-campus users:</strong> If you use an Internet Service Provider other than Wittenberg University, you may access these resources using our proxy server. <a href="/lib/find/remote">Read these instructions</a> for more information.</div>';
 }
    else { $on_campus == true; } 
    if (! isset($subject)) { $subject = "All Subjects"; $curr_subject = 'all';}
 print "<h1>Indexes & Databases: $subject </h1>\n";

 if ($curr_subject == "all") { 
   $subj_string = ""; 
   $one_year_ago = date("Y-m-d", strtotime("1 year ago"));
   $db = new SearchTable();
   $db->q->table('db_new')->where('suppress','N');
   $db->q->where(
       $db->q->orExpr()
       ->where('cancelled','is',null)
       ->where('cancelled','>',$one_year_ago)
   );
   $db->q->order('title');
   //   $query = "SELECT * from db_new WHERE suppress = 'N' and (cancelled is null or cancelled > '$one_year_ago') order by title";
 }
 
 else { // if subject is other than all
     $db = new SearchTable();
     $db->q->table('db_new')
         ->join('db_assoc.id','db_new.ID','inner')
         ->where('suppress','not','Y')
         ->where('subj_code',$curr_subj)
         ->order('title');
 }

   print "<div id=\"nav-tools\" role=\"nav\">\n";
 
 if ($curr_subject == "all") {
   print "<p style=\"font-weight: bold\">";
   print "<a href=\"#A\">A</a> | <a href=\"#B\">B</a> | <a href=\"#C\">C</a> | <a href=\"#D\">D</a> | <a href=\"#E\">E</a> | <a href=\"#F\">F</a> | <a href=\"#G\">G</a> | <a href=\"#H\">H</a> | <a href=\"#I\">I</a> | <a href=\"#J\">J</a> | <a href=\"#L\">L</a> | <a href=\"#M\">M</a> |\n";
   print "<a href=\"#N\">N</a> | <a href=\"#O\">O</a> | <a href=\"#P\">P</a> | <a href=\"#R\">R</a> | <a href=\"#S\">S</a> | <a href=\"#T\">T</a> | <a href=\"#U\">U</a> | <a href=\"#V\">V</a> | <a href=\"#W\">W</a> | <a href=\"#Y\">Y</a>\n";
 }
 print "</p>\n";
 print "<form action=\"\" method=get>\n";
 print "<label for=\"db_list\">Browse by subject: </label>\n";
 $s = new Subjects();
 $s->SubjectPulldown("curr_subj",true,"db_list"); // 1 = autosubmit onChange
 print "<input type=submit value=\"Submit\">\n";
 if (! $curr_subject == "all") { print '<input type=button value="All Subjects" onClick="window.location='."'".DRUPAL_URL."'".'">'.PHP_EOL; }
 print "</form>\n";
 $letter = "";
 $printed = array();

 print '<div id="filter"></div>';
 print '</div><!-- id=nav-tools -->';
 print '<div id="filterable">';

 $rest = '';
 $prime_db = '';

 foreach ($db->q->get() as $myrow) {
   extract($myrow);
   $desc = $short_desc;
   if (preg_match("/(.+)\.$/",$desc,$m)) { $desc = $m[1]; }
   $proxy = $requireproxy;
   if ($proxy == "N") { $proxy = "noproxy"; }
   else {$proxy = ""; }
   $proxy_prob = $proxy_problem;
   $last_letter = $letter;
   if (preg_match ("/^(\w)/",$title,$matches)) {
     $letter = $matches[1];
   }
   $note="";
   if ($proxy_prob == "Y") { 
     $note = "<em>Off-campus users may experience difficulty using this resource.</em>";
   } // end if proxy problem

   if (! array_key_exists('ID', $printed)) {
     $temp = "";
     if ($last_letter != $letter) { 
       $temp .= "<a name=\"$letter\"></a>\n";

     }

     
     //
     // HANDLE for deprecated and cancelled dbs
     //
     //     print "$title: $cancelled, $deprecated<br>";

     $hide = false; 
       if ($cancelled != "" && strtotime($cancelled) < (strtotime("now - 1 year"))) {
	 $hide = true; //don't show db if it's more than 1 year out of use
       }

     if ($cancelled) {
       //     print "$title is cancelled";

       if (time() > strtotime($cancelled)) $url = "";
       $class = " class=\"db_cancelled\"";
       $alternatives = "";
       if ($use_new_db) {
	 $alt_array = preg_split ("/;/", $use_new_db);
	 foreach ($alt_array as $k => $v)
         //                  var_dump($alt_array);
	   $alt_array[$k] = "<a href=\"$url_prefix/redirect.php?$v\">$db_names[$v]</a>";
	 $alternatives = join (" or ", $alt_array);
       }
       $cdate = date("F Y", strtotime($cancelled));
       $c_real_date = date("F d, Y", strtotime($cancelled));
       if (time() > strtotime($cancelled)) { // if cancel date has passed
	 $desc = " -- <span class=\"db_change\">This database was cancelled in $cdate.";
	 if (strlen($alternatives) >0) { $desc .= " Please try $alternatives as a more up-to-date alternative."; }
       } //end if cancel date has passed
       else { // if about to be cancelled
	 $desc = " -- $desc. <span class=\"db_change\">This database will be cancelled on $c_real_date.";
	 if (strlen($alternatives) >0) { $desc .= " We suggest $alternatives as an alternative."; }
       } //end if about to be cancelled
       $desc .= "</span>\n";
       $alternatives = "";
     } //end if cancelled
     
     elseif ($deprecated && (time() > strtotime($deprecated))) {
       $class = " class=\"db_deprecated\"";
       $alt_array = array();

       $alternatives = "";
       if (strlen($use_new_db)>0) { $alt_array1 = preg_split ('/;/', $use_new_db); }
       $cdate = date("F Y", strtotime($cancelled));
       if (isset($alt_array1)) {
           foreach ($alt_array1 as $k => $v) {
               $alt_array[$k] = "<a href=\"$url_prefix/redirect.php?$v\">$db_names[$v]</a>";
           }
           if (sizeof($alt_array)>0) {
               $alternatives = join (" or ", $alt_array);
               $alternatives = "Please try $alternatives as a more up-to-date alternative.";
           }
       else {
           $alternatives = "";
       }
       }
       $ddate= date("F Y", strtotime($deprecated));
       $hidden = "<!-- $provider -->\n";
       $desc = " -- $desc. <span class=\"db_change\">This database is still available but ceased updating in $ddate . $alternatives</span> $note <a href=\"?info=$ID\" class=\"more_info\">More Info</a> $hidden</p>";
     } //end if deprecated
     
     elseif ($see_ref > 0) { // if this is a see reference
       $desc = "Use <a href=\"$url_prefix/redirect.php?$see_ref\">$db_names[$see_ref]</a> (which is the same thing.) <a href=\"?info=$see_ref\" class=\"more_info\">More Info</a> $hidden</p>\n";
     }
     
     else {  // if neither deprecated nor cancelled nor a see-reference
       $class = ""; 
       $desc .= ". $note <a href=\"?info=$ID\" class=\"more_info\">More Info</a> $hidden</p>\n";
     } // if neither deprecated nor cancelled
     

     //
     // PRINT database entries
     //
     
     if ($url || $route_to_db) { $titlestring = "<a href=\"$url_prefix/redirect$proxy.php?$ID\">$title</a>"; }
     else { $titlestring = $title; }
     if ($see_ref > 0) { $titlestring = "<strong>$titlestring</strong>"; }
     //   $temp .= "<p$class>$ft $titlestring\n";
     $temp .= "<p$class>$titlestring\n";
     $temp .= $desc;
     if (! $hide) { 
         if (!isset($primacy)) { $primacy = 'N'; }
       if (($primacy == "Y") && ($subj_code == "general") && ($curr_subj != "general")) {$rest .= $temp;}
       elseif ($primacy == "Y") { $prime_db .= $temp; }
       else { $rest .= $temp; }
     }
     $printed[$ID] = $ID;
   } //end if not already printed
   $see_ref = ""; //for some reason this was not getting replaced with each iteration, so we have to nix it independently (possibly because most see_ref=NULL
 } //end while myrow
 
 if ($prime_db) {
   print "<h4>Top resource(s) in this field:</h4>\n";
   print "<div class=\"db_list\">\n";
   print "$prime_db</div>\n";
   if (isset($rest)) {
     print "<h4>Other useful resources in this field:</h4>\n";
   } // end if rest
 } //end if prime
 print "<div class=\"db_list\">\n";
 print "$rest\n\n";
 $prime_db = $rest = "";
 print "</div>\n";
 print "</div><!-- id=filterable -->";
} //end else if not an individual entry

?>
<!-- Place this script as near to the end of your BODY as possible. -->
<script type="text/javascript">
  (function() {
    var x = document.createElement("script"); x.type = "text/javascript"; x.async = true;
    x.src = (document.location.protocol === "https:" ? "https://" : "http://") + "libraryh3lp.com/js/libraryh3lp.js?13384";
    var y = document.getElementsByTagName("script")[0]; y.parentNode.insertBefore(x, y);
  })();
</script>