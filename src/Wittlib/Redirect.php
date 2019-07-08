<?php

/*
  allowed $conf values:
  'allowed_domains' = array; // list of domain names allowed for url-based redirect
  'use_proxy' = bool; //default = true
  'resolve_now' = bool; //default = true
*/

namespace Wittlib;

use \atk4\dsql\Query;

class Redirect {
    public function __construct ($id=null, $conf=array()) {
        if (! $this->validateInputs($id,$conf)) { die(); }
        $this->debugLog = array();
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
        $this->setIP($ip);
        $this->resolve_now = true; //overriden by $conf
        $this->use_proxy = true; //overriden by $conf
        $this->evalConf($conf); //$conf to override individual settings

        /* for testing purposes, we may not resolve right away */
        if ($this->resolve_now) {
            $this->resolveNow($this->id,$conf);
        }
    }
    
    private function validateInputs($id,$conf) {
        $this->valid_input = false;
        if (preg_match('/^[0-9]+$/',$id)) { 
            $this->valid_input = true;
        }
        elseif (preg_match('/^https*:\/\/([^\/]+)/',$id,$m)) {
            $domain = $m[1];
            if (in_array($domain,$conf['allowed_domains'])) {
                $this->valid_input = true;
            }
        }
        return $this->valid_input;
    }
    private function evalConf($conf) {
        foreach ($conf as $key => $value) {
            $this->$key = $value;
        }
        $this->logAction(__function__);
    }

    public function declareId($id) {
        $this->id = $id;
        $this->logAction(__function__);
    }

    public function setIP($ip = null) {
        if ($ip == null) {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $this->ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        else { 
            $this->ip = $ip;
        }
        $this->logAction(__function__);
    }

    public function getEzproxyPrefix() {
        global $debug;
        if ($this->ip != '' && (! preg_match(CAMPUS_IP_REGEX,$this->ip))) {
            if ($this->use_proxy == true) {
                $this->prepend = PROXY_PREFIX;
            }
            else { 
                $this->prepend = '';
            }
        }
        if ($debug) { print 'PREPEND: '.$this->prepend.PHP_EOL; }
        $this->logAction(__function__);
    }
    
    public function resolveNow($id, $conf=array()) {
        global $debug;
        if ($debug) { print 'Starting to Resolve'.PHP_EOL; }
        if ($conf !== null) {
            $this->evalConf($conf); //rerun to catch test-uses of $conf
        }
        $this->getEzproxyPrefix();
        $this->resolveURL($id);

        if (preg_match('/^http/',$id)) { 
            $this->id = $id;
            $this->url = $id;
            $this->resolved = true;
        }

        if ($this->resolved) { 
            $this->url = $this->prepend . $this->url;
            return true;
        }
        if ($debug) { print 'URL: '.$this->url.PHP_EOL; }
        $this->checkCurrent();
        $this->getReplacements($this->id);
        if ($this->hasErrors()) {
            $this->message = 'Unable to redirect:'.PHP_EOL;
            $this->message .= $this->listErrors();
        }
        $this->logAction(__function__);
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
        elseif (! $this->valid_input) { 
            $this->errors['not_found'] = 'This database was not found.';
        }
        $this->logAction(__function__);
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
        $this->logAction(__function__);
    }
    public function hasErrors() {
        if (sizeof($this->errors) > 0) {
            return true;
        }
        else { 
            return false;
        }
        $this->logAction(__function__);
    }

    public function listErrors() {
        $return = '';
        foreach ($this->errors as $key => $value) {
            $return .= ' * ' . $value . PHP_EOL;
        }
        return $return;
        $this->logAction(__function__);
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
        $this->logAction(__function__);
    }

    public function getTitle($id) {
        $next = $this->c->dsql(); //new Query();
        $next->table('db_new')->where('ID',$id)->field('title');
        return $next->getRow()['title'];
        $this->logAction(__function__);
    }

    private function logAction($note) {
        array_push($this->debugLog, $note);
    }
}
?>