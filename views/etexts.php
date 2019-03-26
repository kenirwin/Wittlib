<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../vendor/autoload.php';
require_once '../config.php';
getConfig('lib');
use \atk4\dsql\Query;
?>
<p><strong>Don&apos;t know where to start? Try: <a href="/redirect.php?214">OhioLINK Electronic Book Center</a></strong></p>
<?php
$db = \atk4\dsql\Connection::connect(DSN,USER,PASS);
$q = $db->dsql();
$now = date('Y-m-d');
$q->table('db_new')
    ->field('title')
    ->field('url')
    ->field('long_desc')
    ->field('short_desc')
    ->field('ID')
    ->where('etext','=','Y')
    ->where('suppress','!=','Y')
    ->where(array(['cancelled','>',$now],['cancelled',null], ['cancelled','=','0000-00-00']))
    ->order('title');

foreach ($q->get() as $myrow) {
extract($myrow);
    if (! $long_desc) { $long_desc = $short_desc; }
    print "    <dt><a href=\"/redirect.php?$ID\">$title</a></dt>\n";
    print "        <dd>$long_desc</dd><p>\n";
  } //#end foreach

?>
</dl>









