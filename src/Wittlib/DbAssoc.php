<?php

namespace Wittlib;

use \atk4\dsql\Query;

class DbAssoc {
    public function __construct () {
        $this->c = \atk4\dsql\Connection::connect(DSN,USER,PASS);
    }
    
    public function listSubjects(){
        $query = $this->c->dsql();
        $query-> table("subjects")
        -> field('subject, subj_code')
        -> where("db_list", "not", "N")
        -> order("subject");
        $this->sqlquery = $query->render();
        $subjectsList = $query->get(); //get results
        return $subjectsList;
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
    
    //return all databases
    public function listDatabases(){
        $query = $this->c->dsql();
        //SELECT * FROM db_new WHERE NOT suppress = 'Y' AND cancelled IS NULL
        $query -> table("db_new")
        -> where([["cancelled" , "=", "NULL"], ["suppress", "not", "Y"]])
        -> field('title, ID')
        -> order("title");  
        $this->sqlquery1 = $query->render();
        $databaseList = $query->get(); //get results
        return $databaseList;
    }   
        
    public function learnAssocSb($subj_code, $primacyStatus = false){
        if ($primacyStatus == false){
            $AssocSbRes = $this->listDBAssoc($subj_code, $primacy=false);
        } elseif ($primacyStatus == true) {
            $AssocSbRes = $this->listDBAssoc($subj_code, $primacy=true);
        }
        
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
        
        $DBAssoc = [];
        foreach ($AssocDbRes as $results){
            array_push($DBAssoc, $results['subj_code']);
        }
        return $DBAssoc;
    }
        
    public function printList($array, $listType){
        if ($listType == "subject") {
            $displayVar = "subject";
            $label = "subj_code";
            $data = "subj_code";
        } else {
            $displayVar = "title";
            $label = "db_id";
            $data = "ID";
        }
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
        $this->deleteExistingSB($formSubmit['subj_code']);
        if (array_key_exists('primary', $formSubmit)){
            $this->updateSBPrim($formSubmit['subj_code'], $formSubmit['primary']);
        }
        
        if (array_key_exists('include', $formSubmit)) {
            $this->updateSBIncl($formSubmit['subj_code'], $formSubmit['include']);
        }
        echo("<em><b>Database association updated successfully<b><em>");
        echo("<br><br>");
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
        $this->deleteExistingDB($formSubmit['db_id']);
        if (array_key_exists('primary', $formSubmit)){
            $this->updateDBPrim($formSubmit['db_id'], $formSubmit['primary']);
        }
        
        if (array_key_exists('include', $formSubmit)) {
            $this->updateDBIncl($formSubmit['db_id'], $formSubmit['include']);
        }
        echo("<em><b>Database association updated successfully<b><em>");
        echo("<br><br>");
        $this->printSBfromDB($formSubmit['db_id']);
    }
    
    public function printSBfromDB($idInput){
        $allSb = $this->listSubjects();
        $assocSB = $this->learnAssocDb($idInput, false);
        $primacySB = $this->learnAssocDb($idInput, true); //true == only get primacy
        
        print '<form><table>'.PHP_EOL;
        print '<thead><tr>
     <td><strong>Include<strong></td>
     <td><strong>Primary<strong></td>
     <td><strong>Title<strong></td>
     </tr></thead>'.PHP_EOL;
        // and print the contents inside
        print '<tbody>'.PHP_EOL;
        $lines = ''; //define an empty list of HTML table lines
        
        foreach ($allSb as $rows) {
            $incSBcheck = '';
            $priSBcheck = '';
            
            if (in_array($rows["subj_code"], $assocSB))  {
                $incSBcheck = "checked";
            }
            
            if (in_array($rows["subj_code"], $primacySB)) {
                $priSBcheck = "checked";
            }
            
            $lines .= '<tr>
            <td><input type = "checkbox" name = "include[]" value='.$rows["subj_code"].' '.$incSBcheck.'></td>
            <td><input type = "checkbox" name = "primary[]" value='.$rows["subj_code"].' '.$priSBcheck.' ></td>
            <td>'.$rows['subject'].'</td>
            </tr>'.PHP_EOL;
        }
        
        print $lines;
        print '</tbody>'.PHP_EOL;
        
        // and close the table
        print '</table>'.PHP_EOL;
        print '<input type="hidden" name="db_id" value='.$idInput.' />'.PHP_EOL;
        print '<input type="submit" name="submit_form" value="Submit Form" />'.PHP_EOL;
        print '</form>'.PHP_EOL;
    }
    
    function printDBfromSB($sucodeInput){
        $allDb = $this->listDatabases();
        $assocDB = $this->learnAssocSb($sucodeInput, false);
        $priDB = $this->learnAssocSb($sucodeInput, true); //true == only get primacy
        
        print '<form><table>'.PHP_EOL;
        print '<thead><tr>
        <td><strong>Include<strong></td>
        <td><strong>Primary<strong></td>
        <td><strong>Title<strong></td>
        </tr></thead>'.PHP_EOL;
        // and print the contents inside
        print '<tbody>'.PHP_EOL;
        $lines = ''; //define an empty list of HTML table lines
        
        foreach ($allDb as $row) {
            $incDBcheck = '';
            $priDBcheck = '';
            
            if (in_array($row["ID"], $assocDB))  {
                $incDBcheck = "checked";
            }
            
            if (in_array($row["ID"], $priDB)) {
                $priDBcheck = "checked";
            }
            
            $lines .= '<tr>
            <td><input type = "checkbox" name = "include[]" value='.$row["ID"].' '.$incDBcheck.'></td>
            <td><input type = "checkbox" name = "primary[]" value='.$row["ID"].' '.$priDBcheck.' ></td>
            <td>'.$row['title'].'</td>
            </tr>'.PHP_EOL;
        }
        
        print $lines;
        print '</tbody>'.PHP_EOL;
        
        // and close the table
        print '</table>'.PHP_EOL;
        print '<input type="hidden" name="subj_code" value='.$sucodeInput.' />'.PHP_EOL;
        print '<input type="submit" name="submit_form" value="Submit Form" />'.PHP_EOL;
        print '</form>'.PHP_EOL;
    }
    
    
     
     //generic build Table from array functions
     function buildTable($array){
         // start table
         $html = '<table>';
         // header row
         $html .= '<tr>';
         foreach($array[0] as $key=>$value){
             $html .= '<th>' . htmlspecialchars($key) . '</th>';
         }
         $html .= '</tr>';
         
         // data rows
         foreach( $array as $key=>$value){
             $html .= '<tr>';
             foreach($value as $key2=>$value2){
                 $html .= '<td>' . htmlspecialchars($value2) . '</td>';
             }
             $html .= '</tr>';
         }
         
         // finish table and return it
         
         $html .= '</table>';
         return $html;
     }
     
    
}

?>

