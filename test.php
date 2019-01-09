<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

use Wittlib\SearchTable;

header('Content-type: text/plain');
$db = new SearchTable();
//print ($db->getLast('pholeos','year'));
$db->booleanAnd('pholeos',['photo'],['genre','title']);
var_dump($db->q->render());
var_dump($db->q->get());
try {

} catch (Exception $e) { 
  print ($e->getMessage());
  }