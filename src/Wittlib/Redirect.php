<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Redirect {
    public function __construct ($id) {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->errors = array();
        $this->resolveURL($id);
        if ($this->hasErrors()) {
            return false;
        }
        $this->checkCurrent();
        $this->getReplacements($this->id);
    }

    private function resolveURL ($id) {
        $this->q = $this->c->dsql(); //new Query();
        $this->q->table('db_new')->where('ID',$id);
        $info_arr = $this->q->get();
        if (array_key_exists(0, $info_arr)) {
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
        else {
            $this->errors['not_found'] = 'This database was not found.';
        }
    }
    private function checkCurrent() {
        if (preg_match('/\d\d\d\d-\d\d-\d\d/',$this->cancelled)) {
            $this->errors['cancelled'] = 'This database ('.$this->title.') was cancelled '.date('M Y', strtotime($this->cancelled)).'.'.PHP_EOL;
        }
        if ($this->suppress == 'Y') { 
            $this->errors['suppressed'] = 'This database ('.$this->title.') is no longer available.'.PHP_EOL;
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
        if ($this->use_new_db !== null) { 
            $replacements = preg_split('/;/',$this->use_new_db);
            $titles = array();
            var_dump($titles);
            var_dump($replacements);
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
    }

    public function getTitle($id) {
        $next = $this->c->dsql(); //new Query();
        $next->table('db_new')->where('ID',$id)->field('title');
        return $next->getRow()['title'];
    }
}
?>