<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Log {
    function __construct() {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->q = $this->c->dsql(); //new Query();
        $this->isBot = false;
        $this->insertedRow = false;
    }
    function logUsage($url, $server) {
        $referer = $agent = $ip = $location = $user_agent = null; 
        if (array_key_exists('HTTP_REFERER',$server)) {
            $referer = $server['HTTP_REFERER'];
        }
        $ip = $server['REMOTE_ADDR'];
        $agent = $server['HTTP_USER_AGENT'];
        $date = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $location = gethostbyaddr($ip);

        $this->isBot = $this->botCheck($agent.$location);

        if (! $this->isBot) {
            try { 
                $this->temp = $this->q->table('redir_log')
                            ->set('url',$url)
                            //            ->set('date',$this->q->expr('now()'))
                            ->set('date',$now)
                            ->set('ip',$ip)
                            ->set('user_agent',$agent)
                            ->set('referer',$referer)
                            ->set('location',$location)
                            ->set('just_date',$date)
                            ->insert();
                $this->insertedRow = true;
            }
            catch (Exception $e) {
                print ($e->getMessage());
            }
        }
    }
    
    function botCheck($agent) {
        include ("Agents.php"); // defines $bots array
        $bot_list = join('|', $bots);
        if (preg_match("/$bot_list/",$agent)) {
            return true;
        }
        else {
            return false;
        }
    }
}