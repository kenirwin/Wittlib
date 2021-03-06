<html>
<head> <title>Database and Subject Assocation</title> </head>
<body> <h1>Database and Subject Assocation</h1>
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
        $formSubmit = $_REQUEST;
        
        /*check request type's Primary/Include,  
          delete existing database connection
          and update new ones from user request*/
        if (array_key_exists('subj_code', $formSubmit)){
            $db->checkPrimIncSB($formSubmit);
            
            if (isset($db->message)) {
                print '<p class = "notice">'.$db->message.'</p>';
            }
            
            //print table again to verify
            $db->printDBfromSB($formSubmit['subj_code']);
        } elseif (array_key_exists('db_id', $formSubmit)){
            $db->checkPrimIncDB($formSubmit);
            
            if (isset($db->message)) {
                print '<p class = "notice">'.$db->message.'</p>';
            }
            
            //print table again to verify
            $db->printSBfromDB($formSubmit['db_id']);
        }       
    }
    
    elseif (isset($_REQUEST['subj_code'])) {
        // list database assocation/checkmark for that subject code
        $sucodeInput = $_REQUEST['subj_code'];
        $db->printDBfromSB($sucodeInput);
    }
    
    elseif (isset($_REQUEST['db_id'])) {
        // list subjects assocation/checkmark for that database id
        $idInput = $_REQUEST['db_id'];       
        $db->printSBfromDB($idInput);
   
         } else {
        if (isset($_REQUEST['list']) && ($_REQUEST['list'] == 'subject')) {
            // list subject links
            $subjects = $db->listSubjects();
            print '<a href="'.$_SERVER['SCRIPT_NAME'].'">Switch to Database view</a>';
            $db->printList($subjects, "subject");
        } else {
            // list database links
            $databases = $db->listDatabases();
            print '<a href="'.$_SERVER['SCRIPT_NAME'].'?list=subject">Switch to Subject view</a>';
            $db->printList($databases, "database");
            }        
        }
                   
} catch (Exception $e){
    var_dump ($e);
}
?>
</body>
</html>