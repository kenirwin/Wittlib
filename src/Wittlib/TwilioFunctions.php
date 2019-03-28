<?php

namespace Wittlib;
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;
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
    
    public function send ($to, $message) {
            $client = new Client($this->sid, $this->token);
    
            try {
                $client->messages->create(
                    $to,
                    array (
                        'from' => $this->from,
                        'body' => $message
                    )
                );
                return true;
            } catch (RestException $e) {
                mail ('kirwin@wittenberg.edu','Twilio Failed',$e);
                return false;
            }
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