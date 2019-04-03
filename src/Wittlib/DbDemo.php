<?php

namespace Wittlib;

use \atk4\dsql\Query;

class DbDemo {
    public function __construct ($id=null, $conf=array()) {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    }

    public function SelectAllDatabases() {
        try {
            $q = $this->c->dsql();
            $q->table('db_new')
                ->field('title')
                ->field('id')
                ->field('url');
            return $q->get();
        } catch (Exception $e) {
            print ($e->getMessage()); 
        }
    }
}
?>
