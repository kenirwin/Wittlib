<?php
/*
use this in the "views/" directory to call the class defined in "src/Wittlib/DbAssoc.php"
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig();

use Wittlib\DbAssoc;

$db = new DbAssoc;


try {
    
    if (isset($_REQUEST['submit_form'])) {
        print 'You did it! You clicked a button.';
        $formSubmit = $_REQUEST;
        print(" ");
        print_r($formSubmit);
                
        //handle submission
        if (array_key_exists('subj_code', $formSubmit)){
                
        } elseif (array_key_exists('db_id', $formSubmit)){
            if (array_key_exists('primary', $formSubmit)){
                $db->updateDBPrim($formSubmit['db_id'], $formSubmit['primary']);
            } 
            
            if (array_key_exists('include', $formSubmit)) {
                $db->updateDBPrim($formSubmit['db_id'], $formSubmit['include']);
                }
            }
    }
    
    elseif (isset($_REQUEST['subj_code'])) {
        // list database settings for that subject code
        $sucodeInput = $_REQUEST['subj_code'];
        $allDb = $db->listDatabases();
        
        $assocDB = $db->learnAssocSb($sucodeInput, false);
        $priDB = $db->learnAssocSb($sucodeInput, true); //true == only get primacy
        
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
    
    elseif (isset($_REQUEST['db_id'])) {
        // list subjects for that database
        $idInput = $_REQUEST['db_id'];
        $allSb = $db->listSubjects();
        
        $assocSB = $db->learnAssocDb($idInput, false);
        $primacySB = $db->learnAssocDb($idInput, true); //true == only get primacy
        
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
    
    else {
        if (isset($_REQUEST['list']) && ($_REQUEST['list'] == 'subject')) {
            // list subject links
            $subjects = $db->listSubjects();
            $db->printList($subjects, "subject");
        } else {
            // list database links
            $databases = $db->listDatabases();
            $db->printList($databases, "database");
   
        }
    }
    
} catch (Exception $e){
    var_dump ($e);
}
?>