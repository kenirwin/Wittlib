<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Redirect {
    public function __construct ($id) {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->errors = array();
        $this->resolveURL($id);
        $this->checkCurrent();
        $this->getReplacements($this->id);
    }

    private function resolveURL ($id) {
        $this->q = $this->c->dsql(); //new Query();
        $this->q->table('db_new')->where('ID',$id);
        $info = $this->q->get()[0];
        $this->id = $info['ID'];
        foreach($info as $k=>$v) {
            $this->$k = $v;
        }
        if (preg_match('/\d+/',$this->route_to_db)) {
            $temp=$this->route_to_db;
            $this->route_to_db = null;
            $this->resolveURL($temp);
        }
    }
    private function checkCurrent() {
        if (preg_match('/\d\d\d\d-\d\d-\d\d/',$this->cancelled)) {
            array_push($this->errors, 'This database was cancelled '.date('M Y', strtotime($this->cancelled)).PHP_EOL);
        }
        if ($this->suppress == 'Y') { 
            array_push($this->errors, 'This database is no longer available: '.$this->title.PHP_EOL);
        }
    }
    public function hasErrors() {
        if (sizeof($this->errors) > 0) {
            return true;
        }
        else { 
            return false;
        }
    }

    public function getReplacements($id) {
        $replacements = preg_split('/;/',$this->use_new_db);
        $titles = array();
        foreach ($replacements as $id) {
            $titles[$id] = $this->getTitle($id);
        }
        if (sizeof($titles) > 0) {
            $urls = 'Try these databases as alternatives: ';
            foreach ($titles as $db_id => $title) {
                $urls .= '<li><a href="/redirect.php?'.$id.'">'.$title.'</a>'.PHP_EOL;
            }
        }
        $this->alternatives = $urls;
    }
    public function getTitle($id) {
        $next = $this->c->dsql(); //new Query();
        $next->table('db_new')->where('ID',$id)->field('title');
        return $next->getRow()['title'];
    }
}
?>