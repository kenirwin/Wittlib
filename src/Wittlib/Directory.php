<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Directory {
    public function __construct() {
        $this->initializeQuery();
    }
    public function displayList($format = 'name') {

    }
    public function getPerson($id) {
        $query = $this->c->dsql();
        $query->table('directory')
            ->where('uniq_id',$id);
        $data = $query->get()[0];
        if ($data['libn'] == 'Y') {
            $data['liaison'] = $this->getSubjects($id);
        }
        return $data;
    }
    public function getSubjects($id) {
        $this->initializeQuery();
        $query = $this->c->dsql();
        $query->table('subjects')
            ->field('subject')
            ->where('liaison',$id)
            ->order('subject');
        $list = $query->get();
        $merged = array();
        foreach ($list as $item) {
            array_push($merged, $item['subject']);
        }
        return (join(', ', $merged));
    }
    public function listLibrarians() {
        $query = $this->c->dsql();
        $query->table('subjects')
            ->join('directory.uniq_id','subjects.liaison')
            ->where('liaison','!=','')
            ->order('subject');
         
        return ($query->get());
        
    }
    private function initializeQuery() {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    }
}