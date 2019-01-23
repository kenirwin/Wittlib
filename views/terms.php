<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');

use Wittlib\SearchTable;

try {
    $db = new SearchTable();
    $return_arr = array();
/* If connection to database, run sql statement. */
if ($db)
  {
    if (isset($_REQUEST['table']) && $_REQUEST['table'] == "indiv") {
        $db->q->table('all_sh_indiv')->field('subject')
            ->where('subject','like','%'.$_REQUEST['term'].'%');
        $indiv = true;
    }
    else { 
        $db->q->table('all_subjects')
            ->where('term','like','%'.$_REQUEST['term'].'%');
      $indiv = false;
    }
    $result = $db->q->get();

    /* Retrieve and store in array the results of the query.*/

    foreach ($result as $row) { 
        $row_array = array();
        if ($indiv) {
            $row_array['id'] = $row['subject'];
            array_push($return_arr,$row_array);
        }
        else {
            $row_array['id'] = $row['term'];
            $row_array['value'] = $row['term'];
            $row_array['label'] = $row['term'] . " (". $row['count'] . ")";
            array_push($return_arr,$row_array);
        } //end else if not indiv table
    }
  }

echo (json_encode($return_arr));

} catch (Exception $e) {
    var_dump($e->getMessage());
}
?>
