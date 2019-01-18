<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Log {
    function __construct() {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->q = $this->c->dsql(); //new Query();
    }
    function logUsage($url, $server) {
        $referer = $agent = $ip = $location = $user_agent = null; 
        if (array_key_exists('HTTP_REFERER',$server)) {
            $referer = $server['HTTP_REFERER'];
        }
        $agent = $server['HTTP_USER_AGENT'];
        $ip = $server['REMOTE_ADDR'];
        $date = date('Y-m-d');
        $location = gethostbyaddr($ip);
        try { 

        $this->q->table('redir_log')
            ->set('url',$url)
            ->set('date',$this->q->expr('now()'))
            ->set('ip',$ip)
            ->set('user_agent',$agent)
            ->set('referer',$referer)
            ->set('location',$location)
            ->set('just_date',$date)
            ->insert();
        var_dump($this->q->render());
        var_dump($this->q->params);
        /*



        */
        var_dump($server);
        }
        catch (Exception $e) {
            print ($e->getMessage());
        }
    }
}