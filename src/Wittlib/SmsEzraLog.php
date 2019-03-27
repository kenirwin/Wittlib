<?php

namespace Wittlib;

use \atk4\dsql\Query;

class SmsEzraLog {
    public function __construct () {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    }

    public function setParams($title,$number,$item,$conf=array('auto' => true)) {
        $this->title = $title;
        $this->number = $number;
        $this->item = $item;
        if (! array_key_exists('auto',$conf) || $conf['auto'] == true) {
            //unless conf[auto] is false, log the stats right away
            $this->logBookInfo();
            $this->updateSmsStats();
        }
    }

    public function logBookInfo() {
        
    }
    
    public function updateSmsStats() {
        
    }

}