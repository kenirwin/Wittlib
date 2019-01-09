<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />

<script type="text/javascript"
         src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js">
</script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');

use Wittlib\SearchTable;

include('./search_form_torch.php');
?>



<style>
#search-echo { font-family: Arial,Verdana,sans-serif;}
.search-terms, .search-fields { font-weight: bold; font-style: italic }
.unfindable { 
    color: #000000; 
 }
.warning { color: green; }
.highlight { font-weight: bold }
#limit_button { padding: 1em }
</style>

<script type="text/javascript"> 
    button_text_original = "Click to Show Only Checked Items";
button_limit_pre = "Limited to ";
button_limit_post= " items. Click to show all results";
checkmark_img = '<img src="<?=$_SERVER['REQUEST_SCHEME'];?>://<?= $_SERVER['SERVER_NAME']; ?>/lib/images/checkmark.png" />';
unchecked_img = '[&nbsp;]';
$(document).ready(function(){
    $('#limit_button a').text(button_text_original); //onLoad, set button text
    $('table#results tbody tr').click(function() { //onClick in table
        if ($('#limit_button a').text() == button_text_original) { //if in expanded mode
            $(this).toggleClass('highlight'); //toggle highlighting
            if ($(this).hasClass('highlight')) { // show check if highlighted
                $(this).children(':first-child').html(checkmark_img);
            }
            else { $(this).children(':first-child').html(unchecked_img); }
        } //end if in expanded mode
        else { //if in reduced mode, toggle the highlighting off, uncheck and hide the row
            $(this).toggleClass('highlight').toggle();
            $(this).children(':first-child').html(unchecked_img);
            $('div#limit_button a').text(button_limit_pre + $('tr.highlight').size() + button_limit_post);
        } //end else if in reduced mode
    }); //end onClick in table
      
    $('div#limit_button a.button').click(function() { // onClick of limit button
        $('table#results tbody tr').each(function() {  //foreach row
            if (! $(this).hasClass('highlight')) {//if NOT highlighted
                $(this).toggle();//toggle show/hide
            }
        }); //end foreach row
        if ($('div#limit_button a').text() == button_text_original) { //if reducing
            //show reduced button
            $('div#limit_button a').text(button_limit_pre + $('tr.highlight').size() + button_limit_post);
        }
        else {$('div#limit_button a').text(button_text_original) } //unreduced button
    }); //end onClick of limit button
}); //end on document-ready
 
</script> 

</head>
<body>
<?php
if (isset($_REQUEST['terms'])) {
    $terms = $_REQUEST['terms'];
    $fields = $_REQUEST['fields'];
    if (sizeof($fields) == 0) {
        $fields = ['subject','title'];
        $notice = "<p class=notice>No fields were selected for searching; your search has been altered to search the Titles and Subjects of articles</p>\n";
 } //end if no fields selected
    $echo_search = "Searching for <span class=\"search-terms\">$terms</span> in <span class=\"search-fields\">". join (", ", $fields) ."</span>";
    if ($_REQUEST['database'] == "torch") { 
        $table = "torch_data";
        $sql_conf['orderby'] = 'year, sql_date,title, subject';
        $display_name = "Torch";
        $alt_link = "<a href=\"?".str_replace("database=torch","database=alum",$_SERVER['QUERY_STRING'])."\">Search for same in the Alumni Magazine</a>";
    }
    elseif ($_REQUEST['database'] == "alum") { 
        $table = "alumni_data"; 
        $sql_conf['orderby'] = 'year, date, title, subject';
        $display_name = "Alumni Magazine";
        $alt_link = "<a href=\"?".str_replace("database=alum","database=torch",$_SERVER['QUERY_STRING'])."\">Search for same in the Torch</a>";
    }
    $db = new SearchTable; //($conf);
    $results = $db->booleanAnd($table,$_REQUEST['terms'],$fields, $sql_conf);
    $num_results = (sizeof($results));

    if ($num_results > 0) {
    
        if (isset($notice)) { print $notice; } 
        
        print "<h3>$num_results articles found in the $display_name | $alt_link</h3>\n"; 
        print "<div id=\"search-echo\">$echo_search</div>\n";
        
        if (! isset($_REQUEST['print_friendly'])) {
            print "<div id=\"print-friendly-link\"><a href=\"https://www6.wittenberg.edu/".$_SERVER['REQUEST_URI']."&print_friendly=true\">Printer-friendly results</a></div>";
        }
        else {
            $_SERVER['QUERY_STRING'] = str_replace('&print_friendly=true', '', $_SERVER['QUERY_STRING']);
            print "<div id=\"print-friendly-link\"><a href=\"https://www.wittenberg.edu/lib/witt_pubs/torch/?".$_SERVER['QUERY_STRING']."\">Return to Library Website</a></div>";
        }
        if ($_REQUEST['database'] == "torch") {
            //  print "| <a href=\"browse.php\">Return to Subject Browse</a></h4>\n";
        }
        
        print ('<div id="limit_button"><a class="button">Click to Show Only Checked Items</a></div>');
        print '<div style="overflow-x:auto;">';
        print "<table border=0 cellspacing=5 id=\"results\">\n";
        print '<thead><tr><th><img src="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'] . '/lib/images/checkmark.png" /></th><th>Subject</th> <th>Article Title</th> <th>Pg:Col</th>'.PHP_EOL;
        print "    <th>Date</th> <th>Year</th></tr></thead><tbody>\n";
        
        foreach ($results as $myrow) {

            extract($myrow);
            // prefer local fulltext to remote URL
            if (isset($fulltext) && ($fulltext != "N")) { $title ="<a href=\"http://".$_SERVER['SERVER_NAME']."/lib/witt_pubs/torch/archive/?file=$fulltext\">$title</a>\n"; }
            elseif ($url) { $title = "<a href=\"$url\">$title</a>\n"; }
            if (preg_match("/$year/", $sql_date)) {
                $date = date_format(date_create($sql_date),'n/j');
            }
            $class = "";
            
            if ($unfindable == "Y") { 
                $class = "class=\"unfindable\""; 
                $title .= "<span class=\"warning\">Note: this title may exist, but the citation to the print edition appears to be incorrect.</span>";
            }
            if ($date_on_cover && ($date_on_cover != $sql_date)) {
                //if the torch issue itself had the incorrect date on the cover
                $title .= "<span class=\"warning\">Note: this issue of the Torch appeared on <b>" . date("m/d/Y", strtotime($sql_date)) . "</b>, but the cover on the printed issue of the Torch incorrectly listed the date as <b>" . date("m/d/Y", strtotime($date_on_cover)) ."</b>. There may be more than one print issue with the same apparent date.</span>\n";
            }
            print "<tr $class><td>[&nbsp;]</td><td>$subject</td><td>$title</td><td>$page</td><td>$date</td><td>$year</td></tr>\n";
        } // foreach db->rows as myrow
        print "</thead></table>";
        print '</div>'; //overflow-x
        } // end if results >0
        else { 
            print "<h3>No Results Found</h3>\n"; 
            $no_results = true;
        }
    } // end if terms
?>
</body>
</html>