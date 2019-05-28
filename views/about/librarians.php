<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../vendor/autoload.php';
require_once '../../config.php';
getConfig('lib');

use Wittlib\Directory;

$db = new Directory;

$data = $db->listLibrarians();

$rows = '';

if (preg_match('/^136.227/',$_SERVER['REMOTE_ADDR'])) {
    //if called directly from campus, not by Drupal
    $dir_link = 'directory.php';
}
else { 
    $dir_link = 'directory';
}


foreach ($data as $row) {
    $name = '<a href="'.$dir_link.'?id='.$row['liaison'].'">'.$row['first_name'].' '.$row['last_name'].'</a>'.PHP_EOL;
    $rows .= '<tr><td>'.$row['subject'].'</td><td>'.$name.'</td></tr>'.PHP_EOL;
}

print '<table>'.PHP_EOL;
print $rows;
print '</table>'.PHP_EOL;

?>
