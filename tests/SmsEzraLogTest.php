<?php
namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use Jchook\AssertThrows\AssertThrows;
use \atk4\dsql\Query;
use Wittlib\SmsEzraLog;
use Wittlib\TwilioFunctions;

define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);
define ('SMS_LOG_SALT','saltydog');

class SmsEzraLogTest extends TestCase {
    use AssertThrows;

    public function setUp() {
        $this->db = new SmsEzraLog();
        //'testtitle','testitem',"Location: CRC WHITE Call \#: PZ8.3.G276 Hn 1991  (AVAILABLE )");
        $this->createTables();
        $this->initializeQuery(); //setup next query
        $this->good_params = array('title'=>'Hop on Pop',
                             'number'=>'9371234567',
                             'item'=>'Location: CRC WHITE'."\n".' Call #: PZ8.3.G276 Hn 1991 (AVAILABLE)');
        $this->alt_params =  array('title'=>'Hop on Pop',
                             'number'=>'937-555-5555',
                             'item'=>'Location: CRC WHITE'."\n".' Call #: PZ8.3.G276 Hn 1991 (AVAILABLE)');
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

    public function testReturnsOkIfHasAllParam() {
        $params = array('title'=>'test','item'=>'test','number'=>'1234567890');
        $this->db->setParams($params);
        $this->assertTrue($this->db->paramsOk);
    }

    public function testReturnsErrorIfNumberNotTenDigits() {
        $ninedigits = array('title'=>'test','item'=>'test','number'=>'123456789');
        $this->db->setParams($ninedigits);
        $this->assertFalse($this->db->paramsOk);        
    }
    
    public function testEncryptsNumberOnParamSubmit() {
        $this->db->setParams($this->good_params);
        $this->assertTrue(isset($this->db->crypt));
    }
    public function testEncryptsAltNumberOnParamSubmit() {
        $this->db->setParams($this->alt_params);
        $this->assertTrue(isset($this->db->crypt));
    }

    public function testPrepsSmsBody() {
        $this->db->setParams($this->good_params);
        $this->assertRegExp('/CRC WHITE/',$this->db->smsBody);
        $this->assertRegExp('/.*Hop on Pop/',$this->db->smsBody);
    }

    public function testParsesItem() {
        $this->db->setParams($this->good_params);
        $this->assertRegExp('/CRC WHITE/',$this->db->location);
        $this->assertRegExp('/Hop on Pop/',$this->db->title);
        $this->assertRegExp('/AVAILABLE/',$this->db->avail);
    }
    
    public function testWritesToSmsRequestLog() {
        $this->db->setParams($this->good_params);
        $this->db->logBookInfo();
        $this->assertTrue($this->db->loggedReqOk);
    }

    public function testIdentifiesNewUser () {
        $this->db->setParams($this->good_params);
        $this->db->checkIfExistingUser();
        $this->assertFalse($this->db->existingUser);
    }
    
    public function identifiesReturngingUser () {
        $this->db->setParams($this->alt_params);
        $this->db->checkIfExistingUser();
        $this->assertTrue($this->db->existingUser);
        $this->assertEquals('Bogus Carrier',$this->carrier);
    }
    /*
    public function testWritesEncryptedNumberToUserLog() {
        $this->db->setParams($this->good_params);
        $this->db->carrier = 'Bogus Carrier';
        $this->db->logUserInfo();
        $this->assertTrue($this->db->loggedUserOk);
    }
    */
    public function testUpdatesReusedNumberInUserLog() {
        /* alt_params defines a user who already has an entry 
           which should increment
        */
        $this->db->setParams($this->alt_params);
        $this->db->carrier = 'Bogus Carrier';
        $this->db->logUserInfo();
        $this->assertTrue($this->db->loggedUserOk);        
        $this->initializeQuery(); 
        $r = $this->db->q->table('sms_users')
           ->field('n')->where('crypt',$this->db->crypt)->get();
        $new_n = $r[0]['n']; // expect 72+1 = 73
        $this->assertEquals(73,$new_n);
    }

    public function testUpdatesCarrierStats() {
        $this->db->setParams($this->alt_params);
        $this->db->carrier = 'Bogus Carrier';
        $this->db->existingUser = true;
        $this->db->logCarrierInfo();
        $this->assertTrue($this->db->loggedCarrierOk);

        $this->initializeQuery(); 
        $r = $this->db->q->table('sms_stats')
           ->field('total')->where('carrier',$this->db->carrier)->get();
        $new_total = $r[0]['total']; // expect 5+1 = 6
        $this->assertEquals(6,$new_total);
    }

    public function testAddNewCarrierWhenNecessary() {
        $this->db->setParams($this->alt_params);
        $this->db->carrier = 'New Carrier';
        $this->db->existingUser = false;
        $this->db->logCarrierInfo();
        $this->assertTrue($this->db->loggedCarrierOk);

        $this->initializeQuery(); 
        $r = $this->db->q->table('sms_stats')
           ->field('total')->where('carrier',$this->db->carrier)->get();
        $new_total = $r[0]['total']; // expect 5+1 = 6
        $this->assertEquals(1,$new_total);
    }

    /* utility functions */

    public function tearDown() {
        unset($this->db);
    }


    private function initializeQuery() {
        $this->db->q = $this->db->c->dsql(); //new Query();
    }


    public function createTables() {
        $sms_reqs_structure = "
CREATE TABLE `sms_reqs` (
  `title` varchar(255) DEFAULT NULL,
  `call` varchar(255) DEFAULT NULL,
  `loc` varchar(255) DEFAULT NULL,
  `avail` varchar(255) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
)";

        $sms_users_structure = "
CREATE TABLE IF NOT EXISTS `sms_users` (
  `id` int(11) NOT NULL,
  `crypt` varchar(255) NOT NULL DEFAULT '',
  `n` int(11) NOT NULL DEFAULT '0',
  `most_recent` date NOT NULL DEFAULT '0000-00-00',
  `carrier` varchar(255) NOT NULL,
  `carrier_updated` date NOT NULL,
  PRIMARY KEY (`id`)
)";
        $num = '9375555555';
        $crypt = crypt($num,SMS_LOG_SALT);
        $sms_users_data = "INSERT INTO `sms_users` (`id`, `crypt`, `n`, `most_recent`, `carrier`, `carrier_updated`) VALUES (1, '$crypt', 72, '2019-03-27', 'Bogus Carrier', '2019-03-18');";

        $sms_stats_structure = "
CREATE TABLE IF NOT EXISTS `sms_stats` (
  `carrier` varchar(50) NOT NULL DEFAULT '',
  `total` int(11) NOT NULL DEFAULT '0',
  `most_recent` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`carrier`)
)";

        $sms_stats_data = "
INSERT INTO `sms_stats` (`carrier`, `total`, `most_recent`) VALUES
('cingular', 465, '2013-03-20'),
('virgin', 224, '2017-08-31'),
('tmobile', 570, '2017-11-15'),
('T-Mobile USA, Inc.', 186, '2019-03-25'),
('nextel', 6, '2012-10-24'),
('cricket', 74, '2017-11-16'),
('centennial', 99, '2013-04-10'),
('credo', 122, '2015-05-03'),
('cinbell', 263, '2014-05-11'),
('AT&T Wireless', 4193, '2019-03-27'),
('Verizon Wireless', 9334, '2019-03-27'),
('Sprint Spectrum, L.P.', 2139, '2019-03-27'),
('US Cellular Corp.', 1, '2018-01-23'),
('Ameritech - PSTN', 2, '2018-06-21'),
('Metro PCS, Inc.', 29, '2019-03-28'),
('Level 3 Communications, LLC', 1, '2018-03-23'),
('Cricket Wireless - ATT - SVR', 57, '2019-02-28'),
('Republic Wireless - Bandwidth.com - Sybase365', 2, '2018-08-28'),
('Bogus Carrier',5,'2018-02-01'),
('Verizon', 1, '2018-08-30');
";
        /*
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crypt` varchar(255) NOT NULL DEFAULT '',
  `n` int(11) NOT NULL DEFAULT '0',
  `most_recent` date NOT NULL DEFAULT '0000-00-00',
  `carrier` varchar(255) NOT NULL,
  `carrier_updated` date NOT NULL,
  PRIMARY KEY (`id`)
        */

        $this->executeQuery($sms_reqs_structure);
        $this->executeQuery($sms_users_structure);
        $this->executeQuery($sms_users_data);
        $this->executeQuery($sms_stats_structure);
        $this->executeQuery($sms_stats_data);
    }

    public function executeQuery($query) {
        $this->initializeQuery();
        $this->db->q->Expr($query)->execute($this->db->c);
    }

}

?>