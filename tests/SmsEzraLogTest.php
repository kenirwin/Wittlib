<?php
namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use Jchook\AssertThrows\AssertThrows;
use \atk4\dsql\Query;
use Wittlib\SmsEzraLog;

define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

class SmsEzraLogTest extends TestCase {
    use AssertThrows;

    public function setUp() {
        $this->db = new SmsEzraLog();
        //'testtitle','testitem',"Location: CRC WHITE Call \#: PZ8.3.G276 Hn 1991  (AVAILABLE )");
        $this->createTables();
        $this->initializeQuery(); //setup next query
    }

    /* tests */

    public function testCreatesDatabaseConnection() {
        $this->assertStringStartsWith(
            'atk4\dsql\Query_',
            get_class($this->db->q)
        );
    }

    public function testReturnsErrorIfEmptyParams() {
        $this->db->setParams(array());
        $this->assertFalse($this->db->paramsOk);
    }

    public function testReturnsErrorIfMissingTitleParam() {
        $missing_title = array('item'=>'test','loc'=>'test','number'=>'1234567890');
        $this->db->setParams($missing_title);
        $this->assertFalse($this->db->paramsOk);
    }

    public function testReturnsErrorIfMissingItemParam() {
        $missing_item = array('title'=>'test','number'=>'1234567890');
        $this->db->setParams($missing_item);
        $this->assertFalse($this->db->paramsOk);
    }

    public function testReturnsErrorIfHasAllParam() {
        $params = array('title'=>'test','item'=>'test','number'=>'1234567890');
        $this->db->setParams($params);
        $this->assertTrue($this->db->paramsOk);
    }

    public function testReturnsErrorIfNumberNotTenDigits() {
        $ninedigits = array('title'=>'test','item'=>'test','number'=>'123456789');
        $this->db->setParams($ninedigits);
        $this->assertFalse($this->db->paramsOk);        
    }

    public function testPrepsSmsBody() {
        $params = array('title'=>'Hop on Pop',
                        'number'=>'9371234567',
                        'item'=>'Location: CRC WHITE; Call #: PZ8.3.G276 Hn 1991 (AVAILABLE)');
        $this->db->setParams($params);
        $this->assertRegExp('/CRC WHITE/',$this->db->smsBody);
        $this->assertRegExp('/.*Hop on Pop/',$this->db->smsBody);
    }


    /* utility functions */

    public function tearDown() {
        unset($this->db);
    }


    private function initializeQuery() {
        $this->db->q = $this->db->c->dsql(); //new Query();
    }


    public function createTables() {
        $db_new_structure = "
CREATE TABLE `db_new` (
`ID`INTEGER,
`title`TEXT,
`url`VARCHAR(255),
`route_to_db`VARCHAR(255),
`deprecated`DATE,
`cancelled`DATE,
`use_new_db`VARCHAR(11),
`suppress`CHAR(1),
PRIMARY KEY(`id`)
)";
        
        $this->executeQuery($db_new_structure);
    }

    public function executeQuery($query) {
        $this->initializeQuery();
        $this->db->q->Expr($query)->execute($this->db->c);
    }




    /*
    private function initializeQuery() {
        $this->q = $this->log->c->dsql(); //new Query();
    }

    private function createTables() {
        $this->createTable("CREATE TABLE IF NOT EXISTS `sms_reqs` (
  `title` varchar(255) DEFAULT NULL,
  `call` varchar(255) DEFAULT NULL,
  `loc` varchar(255) DEFAULT NULL,
  `avail` varchar(255) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) DEFAULT CHARSET=utf8;
");

    }

    private function createTable($query) {
        $this->initializeQuery();
        $this->q->Expr($query)->execute($this->log->c);
    }

    */
}

?>