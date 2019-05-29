<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Directory {
    public function __construct() {
        $this->initializeQuery();
    }
    public function getNames() {
        $query = $this->c->dsql();
        $query->table('directory')
            ->order(['last_name','first_name']);
        return($query->get());
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

    public function getImgHtml($data, $opts = []) {
        if (! array_key_exists('class', $opts)) { $opts['class'] = 'portrait'; }
        if (array_key_exists('link', $opts) && $opts['link'] == true) {
            $link = true;
        } 
        else { $link = false; }

        if (file_exists(STAFF_IMG_DIR.$data['photo'])) {
            $img = '<img src="' . STAFF_IMG_DIR_HTTP.$data['photo'] . '" alt="' . $data['first_name'] .' ' .$data['last_name'].'" class="'.$opts['class'].'" />';
            if ($link && array_key_exists('uniq_id',$data)) {
                $img = '<a href="./directory?id='.$data['uniq_id'].'">' . $img . '</a>'.PHP_EOL;
            }
            return $img;
        }
    }
    public function menuHtml() {
        $menu = '<h2>Staff Directory Options</h2>'
              . '<a href="/lib/about/directory">Library Staff List</a><br />'
              . '<a href="/lib/about/directory/photo">Library Staff Photo Directory</a><br />'
              . '<a href="/lib/about/directory/librarians">Librarians by Subject Area</a>';
        return $menu;
    }
}