<script
  src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
  integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8="
  crossorigin="anonymous"></script>
<script>
$(document).ready(function() {
    $('form').change(function() {
        this.submit();
    });
});
</script>

<style>
dd { text-indent: 2em !important; } 
</style>

<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');

use Wittlib\Honors;

if (array_key_exists('id',$_REQUEST)) {
    DisplayRecord($_REQUEST['id']);
}
elseif (array_key_exists('sort',$_REQUEST)) {
    DisplaySortBy();
    if ($_REQUEST['sort'] == 'author') {
        ListAuthors();
    }
    if ($_REQUEST['sort'] == 'dept') {
        ListDepts();
    }
    else {
        ListYears();
    }
}
else {
    DisplaySortBy();
    ListYears();
}

/* display functions */

function ListYears() {
    ListYearsOrAuthors(true);
}
function ListAuthors() { 
    ListYearsOrAuthors(false);
}

function ListYearsOrAuthors($byYear) {
    try {
    $db = new Honors;
    if ($byYear) { $returnSort='year'; }
    else { $returnSort='author'; }
    $list = $db->getListByAuthor($byYear); // true = year then author
    $output = '';
    foreach ($list as $k => $arr) {
        extract($arr);
        $output .= "<dt>$lastname, $firstname ($year)</dt>\n<dd><a href=\"?id=$id&return_sort=$returnSort\">$title</a></dd>\n";
    }
    print '<dl>'.PHP_EOL.$output.PHP_EOL.'</dl>'.PHP_EOL;
    }catch(Expression $e) {
        print $e->getMessage();
    }
}
    
function ListDepts() {
    $db = new Honors;
    $depts = $db->getDepts();
    foreach ($depts as $dept) {
        print "<dt><strong>$dept</strong></dt>\n";
        $list = $db->getListByAuthor(true,$dept);
        foreach ($list as $row) {
            extract ($row);
            print "<dd>$lastname, $firstname ($year) <a href=\"?id=$id&return_sort=dept\">$title</a></dd>\n";
        }
    }
}

function DisplayRecord($id) {
    $db = new Honors;
    $data = $db->getRecord($id);
    extract ($data);
    $restriction = '';
    $sizenote = '';
    $server_path = '/lib/witt_pubs/honors/';
    if ($suppress == "N") {
        if ($dept2) { $dept1 .= " & $dept2"; }
        if ($perm == "all") { $filepath = "world/"; }
        if ($perm == "campus") { $filepath = "campus/"; }
        $file = '/docs'.$server_path . $filepath . $filename;
        $filesize = round (filesize($file)/1024);
        if ($filesize > 5000) { $sizenote = "<em>Note: This is a very large file; it may be easier to download the file to your computer and open it from there.</em>"; }
        $filesize = $filesize . " KB";
        
        
        // if multiple advisors, display prettily instead of semi-colon separated
        if (preg_match("/;/",$advisor)) {
            $ess = "s";
            $advisors = preg_split ("/;/",$advisor);
            if (sizeof($advisors) == 2) { $advisor = join (" and ",$advisors); }
            else {
                $advisor = "";
                for ($i=0; $i < (sizeof($advisors)-1); $i++) {
                    $advisor .= "$advisors[$i], ";
                }
                $size = sizeof($advisors)-1;
                $advisor .= "and $advisors[$size]";
            } // end else
        } // end if multiple advisors
        
  
        print "<table>\n";
        print "<tr><th>Author</th> <td>$firstname $lastname</td></tr>\n";
        print "<tr><th>Title</th> <td>$title</td></tr>\n";
        print "<tr><th>Department</th> <td>$dept1</td></tr>\n";
        print "<tr><th>Advisor$ess</th> <td>$advisor</td></tr>\n";
        print "<tr><th>Year</th> <td>$year</td></tr>\n";
        if ($univ_honors == "Y") { $type_of_honors = "University Honors"; }
        else { $type_of_honors = "Departmental Honors"; }
        print "<tr><th>Honors</th> <td>$type_of_honors</td></tr>\n";
        if ($pass == "distinction") {
            print "<tr><th>Note</th> <td><img src=\"/lib/images/paw.gif\">Passed with Distinction</td></tr>\n";
        }
        
        if ($published) { 
            print "<tr><th>Publication</th><td>$published</td>\n"; 
        }
        
        if ($perm == "campus") {
            $restriction = "<br><b>At the author's request, an electronic copy of this thesis is only available to on-campus users.</b>";
        }
        if (($perm == "all") || ($perm =="campus")) {
            print "<tr><th>Full Text</th> <td><a href=\"http://".$_SERVER['SERVER_NAME']."/cgi-bin/lib/honors/$filename\" target=\"thesis_view\">View Thesis</a> ($filesize) $sizenote $restriction</td></tr>\n";
        }
        else { print "<tr><th>Full Text</th> <td>The full text of the thesis is not available at this time. Please contact the library for assistance.</td</tr>\n"; }
        if ($abstract) {
            print "<tr><th>Abstract</th> <td>$abstract</td></tr>\n"; 
  }
        print "</table>\n";
    } // end if suppress != Y
    else { print "<p>This record is unavailable at the author's request.</p>\n"; } // end else

    print "<p><a href=\"?sort=".$_REQUEST['return_sort']."\">Return to Main Honors Thesis Archive Page</a></p>\n";    
}

function DisplaySortBy() {
    $selected = ['year'=>'', 'author'=>'', 'dept'=>''];
    if (in_array($_REQUEST['sort'], array_keys($selected))) {
        $sort = $_REQUEST['sort'];
        $selected[$sort] = 'selected';
    }
    $form = '<form method="get" action="">'.PHP_EOL;
    $form.= '<label for="sort">Sort by: </label>'.PHP_EOL;
    $form.= '<select name="sort" id="sort">'.PHP_EOL;
    $form.= '<option value="year" '.$selected['year'].'>Year</option>'.PHP_EOL;
    $form.= '<option value="author" '.$selected['author'].'>Author</option>'.PHP_EOL;
    $form.= '<option value="dept" '.$selected['dept'].'>Department</option>'.PHP_EOL;
    $form.= '</select>'.PHP_EOL;
    $form.= '<noscript><input type="submit" value="go" name="submit_sort"></noscript>'.PHP_EOL;
    $form.= '</form>'.PHP_EOL;
    print $form;
}