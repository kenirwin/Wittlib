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
    
    public function getDistinct(string $table, string $field, array $conf = [])
    {
        if (! array_key_exists('orderby', $conf)) {
            $conf['orderby'] = $field;
        }
        $this->q->table($table)->field($field)->option('distinct');
        $this->applyConf($conf);
        $return = array();
        foreach ($this->q->get() as $row) {
            array_push($return, $row[$field]);
        }
        return $return;
    }

    private function applyConf($conf) {
        if (array_key_exists('direction', $conf)) {
            $direction = $conf['direction'];
        }
        else { $direction = 'ASC'; }
        if (array_key_exists('orderby', $conf)) {
            $this->q->order($conf['orderby'] .' '.$direction);
        }

    }

    public function booleanAnd(string $table, string $terms, array $fields, array $conf = [], bool $return_results = true) {
        $this->q->table($table);
    $terms = preg_split('/\s+/',$terms);
    
    $this->andEveryTermInAnyField($terms, $fields);

    /* KEN: can you replace this with ->applyConf() ? */
    if (array_key_exists('orderby', $conf)) {
        $this->q->order($conf['orderby']);
    }
    if ($return_results) { return $this->q->get(); }
    }
    
    public function andEveryTermInAnyField (array $terms, array $fields) {
        foreach ($terms as $term) {
            $or_arr = array();
            foreach ($fields as $field) {
                $exp = [$field,'like','%'.$term.'%'];
                array_push($or_arr, $exp);
            }
            $this->q->where($or_arr);
        }
    }

    public function andAnyTermInOneField (array $terms, string $field) {
        $or_arr = array();
        foreach ($terms as $term) {
            $exp = [$field,'like',$term];
            array_push($or_arr,$exp);
        }
        $this->q->where($or_arr);
    }
}