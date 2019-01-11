<?php

namespace Wittlib;

use \atk4\dsql\Query;

class Subjects {
    public function __construct () {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
        
        $this->q = $this->c->dsql(); //new Query();
        /*->table('user')->where('id',1)->field('name');
          print($q->render());
          print_r($q->params);
        */
    }

    public function subjectDecode ($subj) {
        try { 
        $this->q->table('subjects')->field('subject')
            ->where('subj_code',$subj);
        $response = $this->q->getOne();
        if (is_string($response)) {
            return $response;
        }
        else {
            return '';
        }
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function subjectPulldown($field_code='', $autosubmit=false, $opt = '') {
  // if $autosubmit is set to true, the script will submit OnChange
        $this->q->table('subjects')
            ->field(['subj_code','subject'])
            ->order('subject');

        if ($opt == "db_list") {
            $this->q->where('db_list','Y');
        }
        elseif ($opt == "reg_list") {
            $this->q->where('registrar_list','Y');
        }
        elseif ($opt == "liaison") {
            $this->q->where('liaison','not','');
        }
        else {
            $this->q->where('journ_only','N');
        }


        $result = $this->q->get();

        $options = '';
        foreach ($result as $myrow) {
            extract($myrow);
            if (isset($curr_subj_code) && $curr_subj_code == $subj_code) { 
                $checked = "SELECTED";
                $curr_subject = $subject;
            }
            else { $checked = ""; }
            $options .= "<option value=$subj_code $checked>$subject</option>\n";
        } //foreach
        
        if ($autosubmit) {  $javascript = "onChange=\"this.form.submit()\""; }
        else { $javascript = ''; }
  
print "<select name=$field_code $javascript><option value=\"\">----- Select a Subject -----</option>\n";
print $options;
print "</select>\n";

    }
}