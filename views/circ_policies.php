<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('lib');

use Wittlib\SearchTable;

if (array_key_exists('ptype',$_REQUEST)) {
    $ptype=$_REQUEST['ptype'];
}
else { $ptype = "student"; }
?>

<style type="text/css" media="all">
<?php 
$css = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/lib/include/library_styles2013.css';
?>
@import url("<?=$css;?>");
</style>

<p><a href="#general">General Policies</a> | <a href="#loan_rules">Circulation Periods / Max Checkouts</a> | <a href="#fines">Overdue Fines</a></p>

<h2>General Policies</h2>
<UL>
<li>A current ID is required for all transactions.</li>
<li>Most books may be checked out for 21 days (or 6 months for faculty members.)</li>
<li>Reserve materials may be checked out with various time limitations. (Ask at the circulation desk for reserve material.)</li>
<li>Holds may be placed on books in circulation (i.e., you can have the book held for you when it is returned.)</li>
<li>Most library materials may be renewed. See chart below for details on loan periods and renewals.</li>
<li><strong>Please note:</strong> Use of audio visual equipment and video programs is limited to faculty, staff, and students of Wittenberg University who are carrying out official University assignments.</li>
</UL>

<a name="loan_rules"></a>
<h2>Circulation Periods and Other Loan Rules</h2>
<h4>How long can you check out a book; how often can you renew it?</h4>
<?php
$ptype_array = array ("student" => "Students",
		      "faculty" => "Faculty",
		      "staff"   => "Staff",
		      "sce"     => "School of Community Ed. students",
		      "hs"      => "High School Scholars Program",
		      "ub"      => "Upward Bound students",
		      "community" => "Community Users",
		      "family"  => "Family of Faculty & Staff",
		      "view_all"=> "View All Patron Types",
		      );
  
PulldownPtypes ($ptype);
if ($ptype=="view_all") {
  foreach ($ptype_array as $ptype_code => $ptype_name) {
    if ($ptype_code != "view_all") {
      print "<h2>$ptype_name</h2>\n";
      ShowLoanRules ($ptype_code);
    } //end if any regular ptype
  } //end foreach pytpe
}//end if view all

else ShowLoanRules($ptype);
?>

<a name="fines"></a>
<h2>Overdue Fines</h2>

<table border=0 cellspacing=5>
<dd>
<tr><th align="left">Stack books<BR>(regular collection)</th>
    <td valign="top">5 &cent;/day per book<br>
                     Replacement cost: $50/book</td></tr>
<tr><th  align="left" valign="top">Reserve books</th>
    <td>Regular reserve - $1/day<BR>
        Overnight reserve - $1/hour<BR>
	In-library only - $1/hour</td></tr>
<tr><th align="left">Curriculum materials</th><td>10&cent;/day</td></tr>
<tr><th  align="left"valign="top">Audio-Visual materials</th>
    <td >5&cent;/day per item<BR>
        $1/day for AV equipment<BR>
	$1/hour for video equipment</td></tr>
<tr><th align="left">OhioLINK books</th>
    <td>$0.50/day overdue fine
    <br>$2.00/day for <b>recalled</b> material
    <br>$50.00 Processing fee for item returned after 30 days overdue
    <br>$125.00 Replacement fee for <b>lost</b> item
</td></tr>
<tr><th align="left">SearchOhio</th>
<td>$0.50/day overdue fine
<br>$25.00 Processing fee per lost item or item returned after 30 days overdue
</td></tr>
</table>

<?php

function PulldownPtypes ($selected_ptype) {
  global $ptype_array, $PHP_SELF;
  $options = '';
  foreach ($ptype_array as $code => $ptype_name) {
    if ($code == $selected_ptype) { 
      $checked = "SELECTED";
    }
    else { $checked = ""; }
    $options .= "<option value=$code $checked>$ptype_name</option>\n";
  } //end foreach

  print "<form action=\"$PHP_SELF#loan_rules\" method=GET>\n";
  print "<span class=\"big_pulldown_label\">Select Patron Type: </span><select name=\"ptype\" onChange=\"this.form.submit()\" class=\"big_pulldown\">\n";
  print $options;
  print "</select>\n";
  print "</form>\n";
}// end function PulldownPtypes


function ShowLoanRules ($ptype) { 
  global $ptype_array;
  $scope_array = array ("main" => "Main Circulating Collection",
			"crc" => "Curriculum Resource Center",
                        "leisure" => "Leisure Collection", 
			"av_audio" => "Audio Materials",
			"av_video" => "Video Materials",
			"ohiolink_book" => "OhioLINK Books, etc",
			"ohiolink_media" => "OhioLINK Audio/Video",
			"searchohio_book" => "SearchOhio Books, Audio Books, Music",                   "searchohio_media" => "SearchOhio DVDs");

  $db = new SearchTable();
  $db->q->table('loan_rules')
      ->where('ptype',$ptype);
  $rows = $db->q->get();
  
  foreach ($rows as $myrow) {
    extract($myrow);
    //  print_r ($myrow);
    $rules[$scope][$criterion] = $value;
  }
  
  $column_headers = "<th>Scope</th> <th>Loan Period<br>(days)</th> <th>Max Renewals</th> <th>Renewal Period<br>(days)</th> <th>Max Items Checked Out</th> <th>Max<br>OhioLINK<br>Holds</th>";
  
  //print_r ($rules);
  
  $scopes_to_display = array("main","crc","leisure","av_audio","av_video","ohiolink_book","ohiolink_media","searchohio_book","searchohio_media");
  $lines = '';
  foreach ($rules as $scope => $criterion) {
    if (in_array($scope, $scopes_to_display)) {
      $other_data = "";
      $style1 = "";
      $style2 = "";

      if ($scope == "ohiolink_book") { 
	if ($rules['ohiolink']['max_checkout'] < 1) {
	  $rules['ohiolink']['max_checkout'] = "";
	  $style1 = "class=\"na\"";
	}
	if ($rules['ohiolink']['max_holds'] < 1) {
	  $rules['ohiolink']['max_holds'] = "";
	  $style2 = "class=\"na\"";
	}
	$other_data = "<td rowspan=2 $style1>".$rules['ohiolink']['max_checkout']."</td> <td rowspan=2 $style2>".$rules['ohiolink']['max_holds']."</td>\n"; }
      
      elseif ($scope == "crc") { 
	$other_data =  "<td>".$rules['crc']['max_crc_checkout']."</td> <td class=\"na\"></td>\n";
      }

      elseif ($scope == "av_audio") {
	$other_data = "<td rowspan=2>". $rules['av']['max_av_checkout'] ."</td> <td rowspan=2 class=\"na\"></td>\n";
      }
      
      elseif ($scope == "main") { 
	$other_data = "<td>".$rules['all']['max_item_checkout']."</td><td class=\"na\"></td>"; 
      }

      $rules_list = array ("loan_period","num_renews","renew_period");

      foreach($rules_list as $rule_name) {
	if ($rules[$scope][$rule_name] != "") 
	  $$rule_name = "<td>".$rules[$scope][$rule_name]."</td>\n";
	
	else 
	  $$rule_name = "<td class=\"na\"></td>\n";
      }
	
	$lines .= "<tr><th>".$scope_array[$scope]."</th> $loan_period $num_renews $renew_period $other_data </tr>\n";

//    $lines .= "<tr><th>".$scope_array[$scope]."</th> <td>".$rules[$scope][loan_period]."</td> <td>".$rules[$scope][num_renews]."</td> <td>".$rules[$scope][renew_period]."</td> $other_data </tr>\n";

    } //if in_array scopes to display
  } //end if we should display this line
  
  print "<table class=\"thin_border\">\n";
  print "<tr>$column_headers</tr>\n";
  print $lines;
  print "<tr><th>Total Max Items Checked Out</th><td colspan=3 class=\"na\"></td><td>".$rules['all']['max_item_checkout']."</td><td class=\"na\"></td>\n";
  print "</table>\n";
} //end function ShowLoanRules
?>


