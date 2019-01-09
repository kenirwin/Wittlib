<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');
use Wittlib\SearchTable;

if (! array_key_exists('index',$_REQUEST)) {
    print 'ERROR: Index parameter required';
}
else {
    define('INDEX',$_REQUEST['index']);
    
    switch ($_REQUEST['index']) {
    case 'eas':
        define('TABLE','eas_index');
        define('FORM','search_form_eas.php');
        break;
    case 'pholeos':
        define('TABLE','pholeos');
        define('FORM','search_form_pholeos.php');
        break;
    }

$db = new SearchTable();
$start_date = $db->getFirst(TABLE,'year');
$db = new SearchTable();
$end_date = $db->getLast(TABLE,'year');
include (FORM);

if (isset($_REQUEST['browse'])) {
    $browse = $_REQUEST['browse'];
    $params = ['orderby' => $browse];
    if ($browse == 'year') {
        $params['direction'] = 'DESC';
    }
    $db = new SearchTable();
    $results = $db->getDistinct(TABLE,$browse,$params);
    foreach ($results as $row) {
        if (($browse == "author")|| ($browse == "year") || ($browse == "title")){
            $srch_str = preg_replace ('|[^A-Za-z0-9]+|', '+', $row);
            print '<BR><a href="?index='.INDEX.'&search='.$srch_str.'&fields='.$browse.'">'.$row.'</a>'.PHP_EOL;
        } // end if browse = author || year
        elseif (($browse == "genre") && ($row != '')) {
            print '<BR><a href="?index='.INDEX.'&search=&genre[]='.$row.'">'.$row.'</a>'.PHP_EOL;
        }
    }
}

elseif (isset($_REQUEST['search']) || isset($genre)) {
    if (array_key_exists('fields',$_REQUEST)) {$fields = $_REQUEST['fields'];}
    else { $fields = ''; }
    if (array_key_exists('genre',$_REQUEST)) {$genre = $_REQUEST['genre'];}
    else { $genre = []; }
    if (($fields == 'any')||($fields=='')) { $fields = array ("title","author","year"); }
    else {$fields = array($fields);}
    
    if ((sizeof($genre) == 0) || ($genre[0]== "any") || (! ($genre))) {
        $db = new SearchTable(); 
        $genre = array_filter($db->getDistinct(TABLE, 'genre'));
    }

    $db = new SearchTable();
    $db->booleanAnd(TABLE,$_REQUEST['search'],$fields, [], false);
    $db->andAnyTermInOneField($genre,'genre');
    //    print($db->q->render());
    $results = $db->q->get();
    $count = sizeof($results);

    if ($count > 0) {
        print "<h3>$count Results</h3>\n";

        echo "<table border=0 cellspacing=10>\n";
        echo "<tr><th align=left>Author</th><th align=left>Title</th><th align=left>Genre</th><th align=left>Volume (Year)</th> <th>Pages</th>\n";
        foreach ($results as $myrow) {
            extract($myrow);
            echo "<tr><td>$author</td> <td>$title</td> <td>$genre</td> <td>$volume ($year)</td> <td>$page</td></tr>\n";
        }
        echo "</table>\n";
    } // end if countable results
}
} //else if index given in request array

?>