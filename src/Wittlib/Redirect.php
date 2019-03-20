<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Redirect {
    public function __construct ($id=null, $resolve_now = true) {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->resolved = false;
        $this->message = '';
        $this->prepend = '';
        $this->ip = '';
        $this->errors = array();
        $this->declareId($id);
        $this->cancelled = false;
        $this->suppress = false;
        $this->use_new_db = false;
        $this->setIP();

        /* for testing purposes, we may not resolve right away */
        if ($resolve_now) {
            $this->resolveNow($this->id);
        }

    }
    
    public function declareId($id) {
        $this->id = $id;
    }

    public function setIP($ip = null) {
        if ($ip == null) {
            if (isset($_ENV['REMOTE_ADDR'])) {
                $this->ip = $_ENV['REMOTE_ADDR'];
            }
        }
        else { 
            $this->ip = $ip;
        }
        $this->getEzproxyPrefix();
    }

    public function getEzproxyPrefix() {
        if ($this->ip != '' && (! preg_match(CAMPUS_IP_REGEX,$this->ip))) {
            $this->prepend = PROXY_PREFIX;
        }
    }
    
    public function resolveNow($id) {
        if (preg_match('/^http/',$id)) { 
            $this->id = $id;
            $this->url = $id;
            $this->resolved = true;
        }

        if ($this->resolved) { 
            $this->url = $this->prepend . $this->url;
            return true;
        }
        $this->resolveURL($id);
        $this->checkCurrent();
        $this->getReplacements($this->id);
        if ($this->hasErrors()) {
            $this->message = 'Unable to redirect:'.PHP_EOL;
            $this->message .= $this->listErrors();
        }
    }

    private function resolveURL ($id) {
        $this->q = $this->c->dsql(); //new Query();
        $this->q->table('db_new')->where('ID',$id);
        $info_arr = $this->q->get();
        if (array_key_exists(0, $info_arr)) {
            $info = $this->q->get()[0];
            $this->id = $info['ID'];
            foreach($info as $k=>$v) {
                $this->$k = $v;       // defines $this->url among others
            }
            if (preg_match('/\d+/',$this->route_to_db)) {
                $temp=$this->route_to_db;
                $this->route_to_db = null;
                $this->resolveURL($temp);
            }
            else {
                $this->resolved = true;
            }
        }
        else {
            $this->errors['not_found'] = 'This database was not found.';
        }
    }
    private function checkCurrent() {
        if (preg_match('/\d\d\d\d-\d\d-\d\d/',$this->cancelled)) {
            $this->errors['cancelled'] = 'This database ('.$this->title.') was cancelled '.date('M Y', strtotime($this->cancelled)).'.'.PHP_EOL;
            $this->resolved = false;
        }
        if ($this->suppress == 'Y') { 
            $this->errors['suppressed'] = 'This database ('.$this->title.') is no longer available.'.PHP_EOL;
            $this->resolved = false;
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

    public function listErrors() {
        $return = '';
        foreach ($this->errors as $key => $value) {
            $return .= ' * ' . $value . PHP_EOL;
        }
    }

    public function getReplacements($id) {
        if ($this->use_new_db !== null) { 
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
            /*
            print 'Titles:'.PHP_EOL; var_dump($titles);
            print 'Alt: '.PHP_EOL;var_dump($replacements);
            */
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