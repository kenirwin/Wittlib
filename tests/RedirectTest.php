<?php

namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use \atk4\dsql\Query;
use Wittlib\Redirect;

define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'config.php';
getProxyConfig();

class RedirectTest extends TestCase {
    public function setUp() {
        $conf = array ('resolve_now' => false);
        $this->db = new Redirect (null,$conf);
        $this->createTable();
        $this->populateTable();
        $this->initializeQuery(); //setup next query
    }

    public function tearDown() {
        unset($this->db);
    }

    public function testCanDeclareId() {
        $this->db->declareId(1);
        $this->assertEquals(
            1,
            $this->db->id
        );
    }

    public function testResolvesSimple() {
        $resolved = [ 1 => 'http://www.nexisuni.com',
                      3 => 'http://www.nexisuni.com'
        ];

        foreach ($resolved as $id => $final_url) {
            $data = $this->db->resolveNow($id);
            $this->assertEquals(
                $final_url,
                $this->db->url
            );

            $this->assertEquals(
                true,
                $this->db->resolved
            );
        }
    }

    public function testReturnsErrorOnCancelled (): void
    {
        $data = $this->db->resolveNow(2);
        $this->assertTrue(
            array_key_exists('cancelled',$this->db->errors)
        );
    }

    public function testResolvedIsFalseOnCancelledNoForwarding(): void
    {
        $data = $this->db->resolveNow(2);
        $this->assertEquals(
            false,
            $this->db->resolved
        );
    }

    public function testReturnsErrorOnSuppressed (): void
    {
        $data = $this->db->resolveNow(4);
        $this->assertTrue(
            array_key_exists('suppressed',$this->db->errors)
        );
    }

    public function testResolvedIsFalseOnSuppressedNoForwarding(): void
    {
        $data = $this->db->resolveNow(4);
        $this->assertEquals(
            false,
            $this->db->resolved
        );
    }

    public function testGeneratesAlternativesOnCancelledWhenPossible (): void
    {
        $data = $this->db->resolveNow(5);
        $this->assertTrue(
            isset($this->db->alternatives)
        );
        $this->assertEquals(
            1,
            preg_match('/Academic Search Complete/',$this->db->alternatives)
        );
    }


    /*    
    public function testSuppressedWithAltGetsErrorMessage(): void
    {
        $data = $this->db->resolveNow(5);
        $this->assertTrue(
            strlen($this->db->message) > 0
        );
    }
    */

    public function testDirectUrlReturnsUrl () {
        $testurl = 'https://search.ebscohost.com/login.aspx?profile=ehost&defaultdb=a9h';
        $data = $this->db->resolveNow($testurl);
        $this->assertEquals(
            $this->db->url,
            $testurl
        );
    }

    public function testOffCampusIpPrependsEzproxy () {
        $off_ip = '123.456.78.910';
        $testurl = 'https://search.ebscohost.com/login.aspx?profile=ehost&defaultdb=a9h';
        $ezprefix = 'https://ezproxy.wittenberg.edu/login?url=';
        $this->db->setIP($off_ip);
        print PHP_EOL.'IP: '.$this->db->ip. PHP_EOL;
        $this->db->resolveNow($testurl);
        $this->assertEquals(
            $this->db->url,
            $ezprefix . $testurl
        );
        
    }

    private function initializeQuery() {
        $this->db->q = $this->db->c->dsql(); //new Query();
    }

    public function createTable() {
        $this->initializeQuery();
        $query = "
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
);
";
        $this->db->q->Expr($query)->execute($this->db->c);
    }

    protected function populateTable() {
        $queries = [
            "INSERT INTO `db_new` VALUES (1,'NexisUni','http://www.nexisuni.com',null,null,null,null,null);",
            "INSERT INTO `db_new` VALUES (2,'Academic Search Complete','http://www.ebscohost.com/asc',null,null,'2016-01-01',null,null);", //cancelled
            "INSERT INTO `db_new` VALUES (3,'LexisNexis','http://www.lexisnexis.com',1,null,null,null,null);", //route to 1
            "INSERT INTO `db_new` VALUES (4,'Axiom','http://firstsearch.org/oldnews',null,null,null,null,'Y');", //suppressed
            "INSERT INTO `db_new` VALUES (5,'That Old Proquest Database','http://proquest.org/oldnews',null,null,'2010-01-01',2,'Y');" //cancelled, use new
        ];
        
        foreach ($queries as $query) {
            $this->initializeQuery();
            $r = $this->db->q->Expr($query)->execute($this->db->c);
        }
    }
}