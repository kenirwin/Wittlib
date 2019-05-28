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

//$data = $db->listLibrarians();

function displayPerson($data) {
    //    print_r ($data);
    $ephone = '+1' . preg_replace('/[^0-9]+/','',$data['phone']);
    $name = $data['first_name'].' '.$data['last_name'];
    $display = '';
    if (file_exists(STAFF_IMG_DIR.$data['photo'])) {
        $display .= '<img src="'.STAFF_IMG_DIR_HTTP.$data['photo'].'" alt="'.$name.'" class="portrait" />';
    }
    $display.= '<h3>'.$name.'<br />'.PHP_EOL;
    $display.= $data['title'].'</h3>'.PHP_EOL;
    $display.= '<address>'.PHP_EOL;
    $display.= 'Phone: <a href="tel:'.$ephone.'">'.$data['phone'].'</a><br />'.PHP_EOL; 
    $display.= 'Email: <a href="mailto:'.$data['uniq_id'].'@wittenberg.edu">'.$data['uniq_id'].'@wittenberg.edu</a>'.PHP_EOL;
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