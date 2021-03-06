<?php

namespace Wittlib;

use \atk4\dsql\Query;
use Twilio\Rest\Client;

class SmsEzraLog {
    public function __construct () {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $this->invalidArgs = array();
        $this->paramsOk = false;
        $this->timestamp = date ('Y-m-d H:i:s');
        $this->smsBody = '';
        $this->carrier = 'unknown';
        $this->loggedReqOk = false;
        $this->loggedUserOk = false;
        $this->loggedCarrierOk = false;
    }

    public function setParams($request) {
        $required = array ('title','number','item');
        foreach ($required as $param) {
            if (array_key_exists($param,$request)) {
                $this->$param = trim($request[$param]);
            } 
            else { array_push($this->invalidArgs, 'Missing required parameter: '.$param); }
        }
        if (isset($this->number)) { $this->validateNumber(); }

        if (sizeof($this->invalidArgs) == 0) {
            $this->paramsOk = true;
            $this->prepSmsBody();
            $this->parseItem();
            $this->crypt = crypt($this->number,SMS_LOG_SALT);
        }

        /*        
        if (! array_key_exists('auto',$conf) || $conf['auto'] == true) {
            //unless conf[auto] is false, log the stats right away
            $this->logBookInfo();
            $this->updateSmsStats();
        }
        */
    }
    
    private function validateNumber() {
        $num = $this->number;
        // remove all non-digits from phone 
        $num = preg_replace("/[^\d]/", "", $num); 
        $this->number = $num; //save version with no punctuation for logging
        if (strlen($num) == 10) {
            $this->toNumber = '+1' . $num;
        }
        else {
            array_push($this->invalidArgs,'Ten-digit phone number required');
            $this->jsReturn = "alert('Ten-digit phone number required');";
        }
    }

    private function prepSmsBody () {
        $body = $this->item . PHP_EOL .'Title: '.$this->title;
        $body = preg_replace ("/\(\s+/", "(", $body);
        $body = stripslashes($body);
        $this->smsBody = $body;
    }

    private function parseItem() {
        $this->item = preg_replace('/\n/',' ',$this->item);
        if (preg_match("/Location: *([A-Z\/ ]+) *Call #: *([^\(]+)\((.*)\)/", $this->item, $m))
        //        if (preg_match("/Location: *([A-Z\/ ]+) *Call \#: *(.*)/", $this->item, $m))
        {
            $this->location = chop($m[1]);
            $this->call = chop($m[2]);
            $this->avail = chop($m[3]);
        }
    }
    
    public function logBookInfo() {
        try {
            $this->q = $this->c->dsql(); //new Query();
            $this->q->table('sms_reqs')
                ->set('title',$this->title)
                ->set('call',$this->call)
                ->set('loc',$this->location)
                ->set('avail',$this->avail)
                ->set('timestamp',$this->timestamp)
                ->insert();
            $this->loggedReqOk = true;
        } catch (Exception $e) {
            $this->loggedReqOk = false;
        }
    }

    public function checkIfExistingUser () {
        $this->q = $this->c->dsql(); //new Query();
        $this->q->table('sms_users')
            ->where('crypt',$this->crypt);
        $r = $this->q->get();
        if (sizeof($r)==0) {
            $this->existingUser = false;
        }
        else {
            $this->existingUser = true;
            $this->carrier = $r[0]['carrier'];
        }
    }

    public function logUserInfo() {
        $date = date ('Y-m-d');
        try {
            $this->q = $this->c->dsql(); //new Query();
            $this->q->table('sms_users')
                ->where('crypt',$this->crypt);
            $existing_user = ($this->q->get());
            if (sizeof($existing_user) > 0) {
                $this->q = $this->c->dsql(); //new Query();
                $n = $existing_user[0]['n'];
                $n++;
                $this->q->table('sms_users')
                    ->set('n',$n)
                    ->update();
                $this->loggedUserOk = true;
            }
            else {
            $this->q = $this->c->dsql(); //new Query();                
            $this->q->table('sms_users')
                ->set('id',null)
                ->set('crypt',$this->crypt)
                ->set('n',1)
                ->set('most_recent',$date)
                ->set('carrier',$this->carrier)
                ->set('carrier_updated',$date)
                ->get();
            $this->loggedUserOk = true;
            }
        } catch (Exception $e) {
            $this->loggedUserOk = false;
            var_dump($e);
        }

    }

    public function logCarrierInfo() {
        try { 
            if ($this->existingUser) {
                $this->q = $this->c->dsql(); //new Query();                
                $this->q->table('sms_stats')
                    ->where('carrier',$this->carrier)
                    ->set('total',$this->q->Expr('total+1'))
                    ->update();
                $this->loggedCarrierOk = true;
            }
            else {
                $this->q = $this->c->dsql(); //new Query();                
                $this->q->table('sms_stats')
                    ->set('carrier',$this->carrier)
                    ->set('total',1)
                    ->set('most_recent',date('Y-m-d'))
                    ->insert();
                $this->loggedCarrierOk = true;
            }
        } catch (Exception $e) {
            print $e->getMessage();
            var_dump($e);
        }
    }
}