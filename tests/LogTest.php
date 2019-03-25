<?php

namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use Jchook\AssertThrows\AssertThrows;
use \atk4\dsql\Query;
use Wittlib\Log;


define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'config.php';
getProxyConfig();

class LogTest extends TestCase {
    use AssertThrows;

    public function setUp() {
        $this->db = new Log();
        $this->createTable();
        $this->initializeQuery(); //setup next query
    }

    /* tests */

    public function testCreatesDatabaseConnection() {
        $this->assertStringStartsWith(
            'atk4\dsql\Query_',
            get_class($this->db->q)
        );
    }

    public function testNoInsertIfBot() {
        $server = array('HTTP_REFERER' => 'http://www.ipl.org',
                        'REMOTE_ADDR' => '136.227.2.38',
                        'HTTP_USER_AGENT' => 'devious_robot'

        );
        $url = '15';
        $response = $this->db->logUsage($url,$server);
        $this->assertFalse($this->db->insertedRow);
        $this->assertTrue($this->db->isBot);
    }

    public function testInsertIfNotBot() {
        $server = array('HTTP_REFERER' => 'http://www.ipl.org',
                        'REMOTE_ADDR' => '136.227.2.38',
                        'HTTP_USER_AGENT' => 'Mozilla 867.53.09'

        );
        $url = '15';
        $response = $this->db->logUsage($url,$server);
        $this->assertTrue($this->db->insertedRow);
        $this->assertFalse($this->db->isBot);
    }

    /* utility functions */

    public function tearDown() {
        unset($this->db);
    }

    private function initializeQuery() {
        $this->db->q = $this->db->c->dsql(); //new Query();
    }

    public function createTable() {
        $this->initializeQuery();
        $query = "
CREATE TABLE `redir_log` (
  `url` varchar(255) NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `referer` mediumtext,
  `location` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `just_date` date NOT NULL DEFAULT '0000-00-00'
)";
        $this->db->q->Expr($query)->execute($this->db->c);
    }

}