<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Honors {
    public function __construct ($id=null, $conf=array()) {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    }
    
    public function getListByAuthor($yearsort = false, $dept = null) {
        $q = $this->c->dsql();
        $q->table('senior_theses')
            ->where('suppress','N')
            ->where('honors','Y');
        if ($yearsort) {
            $q->order('year desc, lastname asc');
        }
        else {
            $q->order('lastname asc');
        }
        // limit by dept if called for
        if ($dept != null) {
            $q->where([
                ['dept1',$dept],
                ['dept2',$dept]
            ]);
        }
        //        return $this->queryString = $q->render();
        return $q->get();
    }
    
    public function getRecord($id) {
        try {
            $q = $this->c->dsql();
            $q->table('senior_theses')
                ->where('honors','Y')
                ->where('suppress','!=','Y')
                ->where('perm','!=','none')
                ->where('id',$id);
            $data = $q->get();
            return $data[0];
        } catch (Exception $e) {
            print ($e->getMessage()); 
        }
    }

    public function getDepts() {
        $q = $this->c->dsql();
        $q->table('senior_theses')
            ->field('dept1,dept2')
            ->where('honors','Y')
            ->where('suppress','!=','Y')
            ->where('perm','!=','none');
        $results = $q->get();
        $depts = array();
        foreach ($results as $row) {
            extract($row);
            if (! array_key_exists($dept1, $depts)) { $depts[$dept1] = $dept1; }
            if (isset($dept2) && ($dept2 != '') && (! array_key_exists($dept2, $depts))) { $depts[$dept2] = $dept2; }
        }
        ksort ($depts);
        return $depts;
    }

    public function getFilepath($id=null) {
        $data = $this->getRow($id);
        $perm = $data[0]['perm'];
        $file = $data[0]['filename'];
        switch ($perm) {
        case 'all': 
            $dir = 'world';
            break;
        case 'campus':
            $dir = 'campus';
            break;
        }
        $path = '/docs/lib/witt_pubs/honors/'.$dir . '/' . $file;
        return $path;
    }
}
?>
