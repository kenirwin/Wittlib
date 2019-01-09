<?php

namespace Wittlib;

use \atk4\dsql\Query;

class SearchTable {

  public function __construct () {
    $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    
    $this->q = $this->c->dsql(); //new Query();
      /*->table('user')->where('id',1)->field('name');
    print($q->render());
    print_r($q->params);
      */
  }
  public function getFirst($table, $field) {
    $this->q->table($table)->field($field)->limit(1,0)->order($field.' ASC');
    $return = $this->q->getOne();
    return $return;
  }
  public function getLast($table, $field) {
    $this->q->table($table)->field($field)->limit(1,0)->order($field.' DESC');
    $return = $this->q->getOne();
    return $return;
  }
  public function booleanAnd(string $table, string $terms, array $fields) {
    $this->q->table($table);
    $terms = preg_split('/\s+/',$terms);

    foreach ($terms as $term) {
      $or_arr = array();
      foreach ($fields as $field) {
	$exp = [$field,'like','%'.$term.'%'];
	array_push($or_arr, $exp);
      }
      $or_str = [$or_arr];
      $this->q->where($or_arr);
    }
  }
}