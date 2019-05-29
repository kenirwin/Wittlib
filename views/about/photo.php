<style>
ul.photo-list { list-style: none; }
ul.photo-list li { display: inline-block; height: 150px; width: 150px; margin: .25em; } 
img.portrait { height: 150px !important;}
</style>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../vendor/autoload.php';
require_once '../../config.php';
getConfig('lib');

use Wittlib\Directory;

$db = new Directory;

$data = $db->getNames();
$no_pic = ''; 
$pics = '';

foreach ($data as $row) {
    if ($row['photo'] == '') {
            $no_pic .= '<li><a href="directory?id='.$row['uniq_id'].'">'.$row['first_name'].' '.$row['last_name'].'</a></li>'.PHP_EOL;
    }
    else { 
        $pics .= '<li>'. $db->getImgHtml($row, ['link'=>true]) . '</li>'.PHP_EOL;
    }
}
print '<p>Click an image for more information</p>'.PHP_EOL;
print '<ul class="photo-list">'.$pics.'</ul>';
print '<h2>Not pictured</h2>'.PHP_EOL;
print '<ul class="not-pictured">'.$no_pic.'</ul>'.PHP_EOL;

print ($db->menuHtml());