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
        //handle submission
    }
    
    elseif (isset($_REQUEST['subj_code'])) {
        // list database settings for that subject code
        $sucodeReq = $_REQUEST['subj_code'];
        $allDb = $db->listDatabases();
        
        $assoc = $db->learnAssocSb($sucodeReq, false);
        print_r($assoc);
        $primacy = $db->learnAssocSb($sucodeReq, true); //true == only get primacy
        
        print '<table>'.PHP_EOL;
        print '<thead><tr>
     <td><strong>Include<strong></td>
     <td><strong>Primary<strong></td>
     <td><strong>Title<strong></td>
     </tr></thead>'.PHP_EOL;
        // and print the contents inside
        print '<tbody>'.PHP_EOL;
        $lines = ''; //define an empty list of HTML table lines
              
        foreach ($allDb as $row) {
            $includecheckmark = '';
            $primarycheckmark = '';
            
            if (in_array($row["ID"], $assoc))  {
                $includecheckmark = "checked";
            } 
            
            if (in_array($row["ID"], $primacy)) {
                $primarycheckmark = "checked";
            }
            
            $lines .= '<tr>
            <td><input type = "checkbox" name = "include[]" value='.$row["ID"].' '.$includecheckmark.'></td>
            <td><input type = "checkbox" name = "primary[]" value='.$row["ID"].' '.$primarycheckmark.' ></td>
            <td>'.$row['title'].'</td>
            </tr>'.PHP_EOL;    
        }
        
        print $lines;
        print '</tbody>'.PHP_EOL;
        
        // and close the table
        print '</table>'.PHP_EOL;
    }
    
    elseif (isset($_REQUEST['db_id'])) {
        // list subjects for that database
        $idInput = $_REQUEST['db_id'];
        $allSb = $db->listSubjects();
        
        $assoc = $db->listSubjAssoc($idInput, false);
        print_r($assoc);
        $primacy = $db->listSubjAssoc($idInput, true); //true == only get primacy
        
        print '<table>'.PHP_EOL;
        print '<thead><tr>
     <td><strong>Include<strong></td>
     <td><strong>Primary<strong></td>
     <td><strong>Title<strong></td>
     </tr></thead>'.PHP_EOL;
        // and print the contents inside
        print '<tbody>'.PHP_EOL;
        $lines = ''; //define an empty list of HTML table lines
        
        foreach ($allSb as $row) {
            $includecheckmark = '';
            $primarycheckmark = '';
            
            if (in_array($row["subj_code"], $assoc))  {
                $includecheckmark = "checked";
            }
            
            if (in_array($row["subj_code"], $primacy)) {
                $primarycheckmark = "checked";
            }
            
            $lines .= '<tr>
            <td><input type = "checkbox" name = "include[]" value='.$row["subj_code"].' '.$includecheckmark.'></td>
            <td><input type = "checkbox" name = "primary[]" value='.$row["subj_code"].' '.$primarycheckmark.' ></td>
            <td>'.$row['subject'].'</td>
            </tr>'.PHP_EOL;
        }
        
        print $lines;
        print '</tbody>'.PHP_EOL;
        
        // and close the table
        print '</table>'.PHP_EOL;
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