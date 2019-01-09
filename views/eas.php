<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');
use Wittlib\SearchTable;

// EAS config
define('TABLE','eas_index');
define('FORM','search_form_eas.php');

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
            print '<BR><a href="?search='.$srch_str.'&fields='.$browse.'">'.$row.'</a>'.PHP_EOL;
        } // end if browse = author || year
        elseif (($browse == "genre") && ($row != '')) {
            print '<BR><a href="?search=&genre[]='.$row.'">'.$row.'</a>'.PHP_EOL;
        }
    }
}

elseif (isset($_REQUEST['search']) || isset($genre)) {
    if (array_key_exists('fields',$_REQUEST)) {$fields = $_REQUEST['fields'];}
    else { $fields = ''; }
    if (array_key_exists('genre',$_REQUEST)) {$genre = $_REQUEST['genre'];}
    else { $genre = []; }
    if (($fields == "any")||($fields=="")) { $fields = array ("title","author","year"); }
    
    if ((sizeof($genre) == 0) || ($genre[0]== "any") || (! ($genre))) {
        $db = new SearchTable(); 
        $genre = array_filter($db->getDistinct(TABLE, 'genre'));
    }

    

}
?>
