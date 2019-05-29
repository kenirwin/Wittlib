<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Associate {
    public function __construct($table, $key, $val) {
        $this->table = $table;
        $this->key = $key;
        $this->val = $val;
    }
    public function getAssoc() {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        $query = $this->c->dsql();
        $query->table($this->table)
            ->field($this->key)
            ->field($this->val);
        print $query->render();
        return ($query->get());
    }
}