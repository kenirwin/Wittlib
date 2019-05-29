<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

<script>
           $(document).ready(function () {
               $('#directory').DataTable({
                       paging: false
                   });
           });
</script>

<style>
img.portrait { float: left; height: 150px !important; margin-right: 1em;; }
</style>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../vendor/autoload.php';
require_once '../../config.php';
getConfig('lib');

use Wittlib\Directory;

$db = new Directory;

if (array_key_exists('id',$_REQUEST)) {
    $data = $db->getPerson($_REQUEST['id']);
    displayPerson($data);
}

else {
    $data = $db->getNames();
    listNames($data);
}

function listNames($data) {
    $depts = array ('ref' => 'Reference',
                    'tech' => 'Technical Services',
                    'circ' => 'Circulation',
                    'admin' => 'Administration',
                    'av' => 'Audio-Visual Services'
    );
    $rows = '';
    foreach ($data as $row) {
        $rows .= '<tr><td><b>'.$row['last_name'].', '.$row['first_name']. '</b><br />'.$row['title'].'</td>'
              . '<td>'.$depts[$row['dept']].'</td>'
              . '<td>'.formatPhone($row['phone']).'</td>'
              . '<td>'.formatEmail($row['uniq_id']).'</td>';        
        
    }
    print '<table id="directory">';
    print '<thead><tr><td>Name</td> <td>Department</td> <td>Phone</td> <td>Email</td></tr></thead>'.PHP_EOL;
    print '<tbody>'.PHP_EOL;
    print $rows;
    print '</tbody>'.PHP_EOL;
    print '</table>'.PHP_EOL;
}
function formatPhone($number) {
   $ephone = '+1' . preg_replace('/[^0-9]+/','',$number);
    return '<a href="tel:'.$ephone.'">'.$number.'</a>';
}
function formatEmail($id) {
    return '<a href="mailto:'.$id.'@wittenberg.edu">'.$id.'@wittenberg.edu</a>';
}
function displayPerson($data) {
    //    print_r ($data);
    $name = $data['first_name'].' '.$data['last_name'];
    $display = '';
    if (file_exists(STAFF_IMG_DIR.$data['photo'])) {
        $display .= '<img src="'.STAFF_IMG_DIR_HTTP.$data['photo'].'" alt="'.$name.'" class="portrait" />';
    }
    $display.= '<h3>'.$name.'<br />'.PHP_EOL;
    $display.= $data['title'].'</h3>'.PHP_EOL;
    $display.= '<address>'.PHP_EOL;
    $display.= 'Phone: '.formatPhone($data['phone']).'<br />'.PHP_EOL; 
    $display.= 'Email: '.formatEmail($data['uniq_id']).PHP_EOL;
    $display.= '</address>'.PHP_EOL;
    if (array_key_exists('liaison', $data)) { 
        $display.= '<p><b>Liaison to:</b> '.$data['liaison'].'</p>';
    }    
    print $display;


    include('/docs/lib/include/DOM/simple_html_dom.php'); 
    $base_url = 'https://www.wittenberg.edu/lib/bios/';
    $allowed = array('amizikar','dlehman','kirwin','petersk','ssmailes');

    $id = $data['uniq_id'];
    if (in_array($id, $allowed)) {
        if ($html = file_get_html($base_url . $id)) {
            $divs = $html->find('div[class=detail]',0);
            print $divs->innertext();
        }
    }

}

?>