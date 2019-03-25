<?php

namespace Wittlib;

use \atk4\dsql\Query;

class EzproxyConfig {
    public function __construct() {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->output = '';
        $this->getHeaders();
        $this->getPreliminaryEntries();
        $this->getDatabaseEntries();
        $this->getAddenda();
    }
    
    public function printOutput() {
        print $this->output;
    }

    private function getHeaders() {
        $this->addToOutput('ezp-v6-top.txt');
    }

    private function getPreliminaryEntries() {
        $this->addToOutput('ezproxy_special_config.txt');
        $this->addToOutput('open-access-proxy.txt');
    }
    
    private function getDatabaseEntries() {
        $this->q = $this->c->dsql(); //new Query();
        $this->q->table('db_new')
            ->field('title')
            ->field('url')
            ->field('proxy_config')
            ->where('suppress','!=','Y')
            ->where(array(['cancelled','=',''],['cancelled',null],['cancelled','>','now()']));
        
        foreach ($this->q->get() as $row) {
            extract ($row);
            if (preg_match ("/([^\.\/]+)\.(com|org|edu|net|ru)/", $url, $matches)) {
                $domain = "$matches[1].$matches[2]";
            }
            if (preg_match ("/http:\/\/([^\/]+)/", $url, $matches)) {
                $host = "$matches[1]";
            }
            
            if (strlen($proxy_config) > 0) {
                $this->output .=  "$proxy_config\n\n";
            }
            
            
            else {
                $this->output .= "T $title\n";
                $this->output .= "U $url\n";
                $this->output .= "D $domain\n";
                $this->output .= "H $host\n";
            }
            $this->output .= "\n";
        }

        /*
        print $this->q->render();
        var_dump($this->q->params);
        */

    }
    
    private function getAddenda() {
        $this->output .= 'IncludeFile ebscoejs.txt'.PHP_EOL;
        $this->addToOutput ('ezproxy_extra_hosts.txt');
        $this->addToOutput ('ezproxy_oxford_config.txt');
    }

    private function addToOutput($file) {
        $this->output .= PHP_EOL . ' # BEGIN FILE: ' . $file . PHP_EOL;
        $this->output .= file_get_contents (PROXY_CONFIG_PATH . $file);
        $this->output .= PHP_EOL . ' # END FILE: ' . $file . PHP_EOL;
    }
}