<?php

namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use \atk4\dsql\Query;
use Wittlib\Redirect;

define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

class RedirectTest extends TestCase {
    public function setUp() {
        $this->db = new Redirect (null,false);
        $this->createTable();
        $this->populateTable();
        $this->initializeQuery(); //setup next query
    }

    public function tearDown() {
        unset($this->db);
    }
    /*
    public function testHasThreeRows() {
        // this tests the setup, not the Redirect class
        $data = $this->db->q->table('db_new')->get();
        $this->assertEquals(
            3,
            sizeof($data)
        );
    }
    */
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
        }
    }

    public function testReturnsErrorOnCancelled (): void
    {
        $data = $this->db->resolveNow(2);
        $this->assertTrue(
            array_key_exists('cancelled',$this->db->errors)
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
            "INSERT INTO `db_new` VALUES (3,'LexisNexis','http://www.lexisnexis.com',1,null,null,null,null);" //route to 1
        ];
        
        foreach ($queries as $query) {
            $this->initializeQuery();
            $r = $this->db->q->Expr($query)->execute($this->db->c);
        }
    }
}