<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('witt_pubs');

use Wittlib\SearchTable;

header('Content-type: text/plain');
$db = new SearchTable();
//print ($db->getLast('pholeos','year'));
$r = $db->booleanAnd('pholeos','photo',['genre','title']);
var_dump($r);
//var_dump($db->getDistinct('eas_index','genre'));
//var_dump($db->q->render());

try {

} catch (Exception $e) { 
  print ($e->getMessage());
  }