<?php

namespace Wittlib;

class Stats {
    public function loadData($arr) {
        $this->data = $arr;
    }
    
    public function sum() {
        return array_sum($this->data);
    }
    
    public function mean() {
        return array_sum($this->data) / count($this->data);
    }
    
    public function min(){
        return min($this->data);
    }
    
    public function max(){
        return max($this->data);
    }
}

?>
