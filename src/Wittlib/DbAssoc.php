<?php

namespace Wittlib;

use \atk4\dsql\Query;

class DbAssoc {
    public function __construct () {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    }
    
    public function listSubjects(){
        $query = $this->c->dsql();
        /* SELECT subject, subj_code FROM db_new
           WHERE NOT suppress = 'Y' 
           ORDER BY subject */
        $query-> table("subjects")
        -> field('subject, subj_code')
        -> where("db_list", "not", "N")
        -> order("subject");
        $this->sqlquery = $query->render();
        $subjectsList = $query->get(); //get results
        return $subjectsList;
    }
            
    //return all databases
    public function listDatabases(){
        $query = $this->c->dsql();
        /* SELECT title, ID FROM db_new  
           WHERE NOT suppress = 'Y' AND cancelled = NULL 
           ORDER BY title */
        $query -> table("db_new")
        -> where([["cancelled" , "=", "NULL"], ["suppress", "not", "Y"]])
        -> field('title, ID')
        -> order("title");  
        $this->sqlquery1 = $query->render();
        $databaseList = $query->get(); //get results
        return $databaseList;
    }   
    
    public function printDBName($db_id){
        $query = $this->c->dsql();
        $query -> table("db_new")
        -> where("ID", $db_id)
        -> field('title');
        $this->sqlquery1 = $query->render();
        $dbArray = $query->get(); //get results
        return $dbArray;
    }
    
    public function printSBName($subj_code){
        $query = $this->c->dsql();
        $query -> table("subjects")
        -> where("subj_code", $subj_code)
        -> field('subject');
        $this->sqlquery1 = $query->render();
        $dbArray = $query->get(); //get results
        return $dbArray;
    }
    
    
    
    public function listDBAssoc($subj_code, $primacy=false){
        $query = $this->c->dsql();
        if ($primacy == false){
            $query-> table("db_assoc")
            -> where("subj_code", $subj_code)
            -> where("primacy", "N")
            -> field('id')
            -> order("id");
        } else {
            $query-> table("db_assoc")
            -> where("subj_code", $subj_code)
            -> where("primacy", "Y")
            -> field('id, primacy')
            -> order("id");
        }
        $this->sqlquery = $query->render();
        $subjectsList = $query->get(); //get results
        return $subjectsList;
    }
    
    public function listSubjAssoc($db_id, $primacy=false){
        $q = $this->c->dsql();
        if ($primacy == false){
            $q-> table("db_assoc")
            -> where("id", $db_id)
            -> where("primacy", "N")
            -> field('subj_code')
            -> order("subj_code");
        } else {
            $q-> table("db_assoc")
            -> where("id", $db_id)
            -> where("primacy", "Y")
            -> field('subj_code, primacy')
            -> order("subj_code");
        }
        
        $this->sqlquery = $q->render();
        $subjectsList = $q->get(); //get results
        return $subjectsList;
    }
    
        
    public function learnAssocSb($subj_code, $primacyStatus = false){
        if ($primacyStatus == false){
            $AssocSbRes = $this->listDBAssoc($subj_code, $primacy=false);
        } elseif ($primacyStatus == true) {
            $AssocSbRes = $this->listDBAssoc($subj_code, $primacy=true);
        }
        
        //filter just ID from array
        $SBAssoc = [];
        foreach ($AssocSbRes as $result){
            array_push($SBAssoc, $result['id']);
        }
        return $SBAssoc;
    }
    
    public function learnAssocDb($db_id, $primacyStatus = false){
        if ($primacyStatus == false){
            $AssocDbRes = $this->listSubjAssoc($db_id, $primacy=false);
        } elseif ($primacyStatus == true) {
            $AssocDbRes = $this->listSubjAssoc($db_id, $primacy=true);
        }
        
        //filter just subj_code from array
        $DBAssoc = [];
        foreach ($AssocDbRes as $results){
            array_push($DBAssoc, $results['subj_code']);
        }
        return $DBAssoc;
    }
        
    public function printList($array, $listType){
        //adaptive print all Database/Subject
        if ($listType == "subject") {
            $displayVar = "subject";
            $label = "subj_code";
            $data = "subj_code";
        } else {
            $displayVar = "title";
            $label = "db_id";
            $data = "ID";
        }
        
        //HTML output for table
        $lines = ''; //define an empty list of HTML table lines
        foreach ($array as $row) {
            $lines .= '<li><a href ="'.$_SERVER['PHP_SELF'].'?'.$label.'='.$row[$data].'">'.$row[$displayVar].'</a></td>
            </li>'.PHP_EOL;
        }             
        print '<ul>'.PHP_EOL;
        print $lines;
        print '</ul>'.PHP_EOL;        
    }
    
    public function deleteExistingSB($subj_code){
        $deleteQuery = $this->c->dsql();
        
        $deleteQuery->table('db_assoc')
        ->where('subj_code', $subj_code)
        ->delete();
        $deleteQuery->render();
    }
    
    public function updateSBPrim($subj_code, $array){
        //go through each item in array to insert new items
        foreach ($array as $primArray){
            $query = $this->c->dsql();
            $query->table('db_assoc')
            ->set('subj_code', $subj_code)
            ->set('id', $primArray)
            ->set('primacy', 'Y')
            ->insert();
            $query->render();
        }
    }
    
    public function updateSBIncl($subj_code, $array){
        //go through each item in array to insert new items
        foreach ($array as $incArray){
            $query = $this->c->dsql();
            $query->table('db_assoc')
            ->set('subj_code', $subj_code)
            ->set('id', $incArray)
            ->set('primacy', 'N')
            ->insert();
            $query->render();
        }
    }
    
    public function checkPrimIncSB($formSubmit){
        //delete existing association in db_assoc
        $this->deleteExistingSB($formSubmit['subj_code']);
        
        //update association for primary
        if (array_key_exists('primary', $formSubmit)){
            $this->updateSBPrim($formSubmit['subj_code'], $formSubmit['primary']);
        }
        
        //update association for regular
        if (array_key_exists('include', $formSubmit)) {
            $this->updateSBIncl($formSubmit['subj_code'], $formSubmit['include']);
        }
        
        //update successfully message
        echo("<em><b>Database association updated successfully<b><em>");
        echo("<br><br>");
        
        //print table again to verify
        $this->printDBfromSB($formSubmit['subj_code']);    
    }
    
    
    public function deleteExistingDB($newID){
        $deleteQuery = $this->c->dsql();
        $deleteQuery->table('db_assoc')
        ->where('id', $newID)
        ->delete();
        $deleteQuery->render();
    }
    
    public function updateDBPrim($newID, $array){      
        //go through each item in array to insert new items
        foreach ($array as $primArray){
            $query = $this->c->dsql();
            $query->table('db_assoc')
                ->set('id', $newID)
                ->set('subj_code', $primArray)
                ->set('primacy', 'Y')             
                ->insert();
           $query->render();
        }
    }
    
    public function updateDBIncl($newID, $array){        
        //go through each item in array to insert new items
        foreach ($array as $incArray){
            $query = $this->c->dsql();
            $query->table('db_assoc')
            ->set('id', $newID)
            ->set('subj_code', $incArray)
            ->set('primacy', 'N')
            ->insert();
            $query->render();
        }
    }
    
    public function checkPrimIncDB($formSubmit){
        //delete existing association in db_assoc
        $this->deleteExistingDB($formSubmit['db_id']);
        
        //update association for primary
        if (array_key_exists('primary', $formSubmit)){
            $this->updateDBPrim($formSubmit['db_id'], $formSubmit['primary']);
        }
        
        //update association for regular
        if (array_key_exists('include', $formSubmit)) {
            $this->updateDBIncl($formSubmit['db_id'], $formSubmit['include']);
        }
        
        //update successfully message
        echo("<em><b>Database association updated successfully<b><em>");
        echo("<br><br>");
        
        //print table again to verify
        $this->printSBfromDB($formSubmit['db_id']);
    }
    
    //get subjects connections from database id
    public function printSBfromDB($idInput){
        $allSb = $this->listSubjects();
        
        //arrays of include/primary association from db_assoc
        $assocSB = $this->learnAssocDb($idInput, false);
        $primacySB = $this->learnAssocDb($idInput, true); //true == only get primacy
        
        //print Database name on top
        echo "<b><i>Database Name: <i><b>";
        print($this->printDBName($idInput)[0]['title']);
        
        //print HTML table      
        print '<form><table>'.PHP_EOL;
        print '<thead><tr>
        <td><strong>Include<strong></td>
        <td><strong>Primary<strong></td>
        <td><strong>Title<strong></td>
         </tr></thead>'.PHP_EOL;
        // and print the contents inside
        print '<tbody>'.PHP_EOL;
        $lines = ''; //define an empty list of HTML table lines
        
        //go through each item in Databases list to check connection
        foreach ($allSb as $rows) {
            $incSBcheck = '';
            $priSBcheck = '';
            
            //checkmark in include column if assocation exists
            if (in_array($rows["subj_code"], $assocSB))  {
                $incSBcheck = "checked";
            }
            
            //checkmark in primary column if assocation exists
            if (in_array($rows["subj_code"], $primacySB)) {
                $priSBcheck = "checked";
            }
            
            //HTML output for table rows
            $lines .= '<tr>
            <td><input type = "checkbox" name = "include[]" value='.$rows["subj_code"].' '.$incSBcheck.'></td>
            <td><input type = "checkbox" name = "primary[]" value='.$rows["subj_code"].' '.$priSBcheck.' ></td>
            <td>'.$rows['subject'].'</td>
            </tr>'.PHP_EOL;
        }
        
        //HTML output for table rows
        print $lines;
        print '</tbody>'.PHP_EOL;
        
        // and close the table
        print '</table>'.PHP_EOL;
        print '<input type="hidden" name="db_id" value='.$idInput.' />'.PHP_EOL;
        print '<input type="submit" name="submit_form" value="Submit Form" />'.PHP_EOL;
        print '</form>'.PHP_EOL;
    }
    
    //get Database connections from subject_code
    function printDBfromSB($sucodeInput){
        $allDb = $this->listDatabases();
        
        //arrays of include/primary association from db_assoc
        $assocDB = $this->learnAssocSb($sucodeInput, false);
        $priDB = $this->learnAssocSb($sucodeInput, true); //true == only get primacy
        
        //print Subject Name on top
        echo "<b><i>Subject Name: <i><b>";
        print($this->printSBName($sucodeInput)[0]['subject']);
        
        //print HTML table
        print '<form><table>'.PHP_EOL;
        print '<thead><tr>
        <td><strong>Include<strong></td>
        <td><strong>Primary<strong></td>
        <td><strong>Title<strong></td>
        </tr></thead>'.PHP_EOL;
        // and print the contents inside
        print '<tbody>'.PHP_EOL;
        $lines = ''; //define an empty list of HTML table lines
        
        //go through each item in Databases list to check connection
        foreach ($allDb as $row) {
            $incDBcheck = '';
            $priDBcheck = '';
            
            //checkmark in include column if assocation exists
            if (in_array($row["ID"], $assocDB))  {
                $incDBcheck = "checked";
            }
            
            //checkmark in primary column if assocation exists
            if (in_array($row["ID"], $priDB)) {
                $priDBcheck = "checked";
            }
            
            //HTML output for table rows
            $lines .= '<tr>
            <td><input type = "checkbox" name = "include[]" value='.$row["ID"].' '.$incDBcheck.'></td>
            <td><input type = "checkbox" name = "primary[]" value='.$row["ID"].' '.$priDBcheck.' ></td>
            <td>'.$row['title'].'</td>
            </tr>'.PHP_EOL;
        }
        
        //HTML output for table rows
        print $lines;
        print '</tbody>'.PHP_EOL;
        
        // and close the table
        print '</table>'.PHP_EOL;
        print '<input type="hidden" name="subj_code" value='.$sucodeInput.' />'.PHP_EOL;
        print '<input type="submit" name="submit_form" value="Submit Form" />'.PHP_EOL;
        print '</form>'.PHP_EOL;
    }    
}

?>

