<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig();

use Wittlib\DbDemo;

/* Your code goes here */ 

try {
    $db = new DbDemo();

    $all = $db->SelectAllDatabases();
    print '<pre>';
    print_r($all);
    print '</pre>';
    


    /* get all */
    $q = $db->c->dsql(); // instantiate a new query
    $q->table('db_assoc'); // select * from `db_assoc`
    $results = $q->get();

    /* get where */
    $q = $db->c->dsql(); // instantiate a new query
    $q->table('db_assoc')
        ->where('subj_code','film');
    $results = $q->get();
    /*
      select * from `db_assoc` where `subj_code` = :a
      Array ( [:a] => film )
    */
    

    /* get where with order */
    $q = $db->c->dsql(); // instantiate a new query
    $q->table('db_assoc')
        ->where('subj_code','film')
        ->order('id');
    $results = $q->get();
    /*
      select * from `db_assoc` where `subj_code` = :a order by `id`
      Array ( [:a] => film )
    */

    /* where is comparative (greater/lesser/etc) */
    $q = $db->c->dsql(); // instantiate a new query
    $q->table('db_assoc')
        ->where('id','<',300)
        ->order('id');
    $results = $q->get();

    /*
      select * from `db_assoc` where `id` < :a order by `id`
      Array ( [:a] => 300 )
    */

    /* multi-table query */
    $q = $db->c->dsql(); // instantiate a new query
    $q->table(['db_assoc','db_new'])
        ->where('db_assoc.id',$q->expr('db_new.id'))
        ->where('db_new.id',19);
    $results = $q->get();

    print ($q->render().'<br>'.PHP_EOL);
    print_r ($q->params);
    print ("<p>Results rows:". sizeof($results));
    print "<pre>";
    print_r($results);
    print "</pre>";
} catch (Exception $e) { 
  print ($e->getMessage());
  var_dump($e);
  }
