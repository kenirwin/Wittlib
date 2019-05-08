<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');

use Wittlib\Honors;

if (array_key_exists('id',$_REQUEST)) {
    DisplayRecord();
}
elseif (array_key_exists('list',$_REQUEST)) {
    if ($_REQUEST['list'] == 'author') {
        ListAuthors(true);
    }
    if ($_REQUEST['list'] == 'dept') {
        ListDepts();
    }
    else {
        ListYears();
    }
}
else {
    ListYears();
}

/* display functions */

function ListYears() {
    ListYearsOrAuthors(true);
}
function ListAuthors() { 
    ListYearsOrAuthors(false);
}

function ListYearsOrAuthors($byYear = false, $returnSort='year') {
    try {
    $db = new Honors;
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
