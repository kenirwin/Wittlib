<?php

namespace Wittlib;
use Twilio\Rest\Client;
/*
  in application, include config from app root 
  and call getConfig()
  to set Twilio config constants
*/

class TwilioFunctions {
    public function __construct ($sid,$token,$from) { 
        $this->sid = $sid;
        $this->token = $token;
        $this->from = $from;
    }
    public function carrierLookup ($number) {
        $client = new Client($this->sid, $this->token);
        
        $info = $client->lookups
              ->phoneNumbers($number)
              ->fetch(
                  array('type'=>'carrier')
              );
        
        return ($info->carrier['name']);
    }
}