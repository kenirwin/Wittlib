<?php

namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use Wittlib\SearchTable;
use \atk4\dsql\Query;


define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

class SearchTableTest extends TestCase {
    public function setUp() {
        $this->db = new SearchTable();
        $this->createTable();
        $this->populateTable();
        $this->db->initializeQuery(); //setup next query
    }

    public function tearDown() {
        unset($this->db);
    }

    public function testConnectByAtk () {
        $this->assertInternalType(
            'object',
            $this->db->c,
            'this->db->c should be an object'
        );
        $this->assertEquals(
            'atk4\dsql\Connection',
            get_class($this->db->c),
            'this->db->c should be a dsql Connection'
        );
        $this->assertEquals(
            'Wittlib\SearchTable',
            get_class($this->db),
            'this->db should be a Wittlib\SearchTable'
        );
    }

    public function testDatabaseHasThreeRows(): void
    {
        $data = $this->db->q->table('items')->get();
        $this->assertEquals(
            3,
            sizeof($data)
        );
    }

    public function testCorrectlyGetsFirstField(): void
    {
        $data = $this->db->getFirst('items','name');
        $this->assertEquals(
            'Candy',
            $data
        );
    }

    public function testCorrectlyGetsLastField(): void
    {
        $data = $this->db->getLast('items','name');
        $this->assertEquals(
            'TShirt',
            $data
        );
    }

    public function testDistinctPriceReturnsTwoValues(): void
    {
        $data = $this->db->getDistinct('items','price');
        $this->assertEquals(
            2,
            sizeof($data)
        );
    }

    public function testReturnsDistinctValues(): void
    {
        $data = $this->db->getDistinct('items','price');
        $this->assertNotEquals(
            $data[0],
            $data[1]
        );
    }


    public function testReturnFieldsGetsCorrectFields(): void
    {
        $this->db->q->table('items');
        $this->db->returnFields(['id','name']);
        $data = $this->db->q->get();
        $this->assertEquals(
            2,
            sizeof($data[0])
        );

        $this->assertTrue(
            array_key_exists('id',$data[0])
        );

        $this->assertTrue(
            array_key_exists('name',$data[0])
        );
        
        $this->assertFalse(
            array_key_exists('price',$data[0])
        );
    }
    /* need to add tests for:
       applyConf()
       booleanAnd()
       andEveryTermInAnyField()
       andAnyTermInOneField()
    */
    
    protected function createTable() {
        $this->db->initializeQuery();
        $query = "
CREATE TABLE `items` (
`id`INTEGER,
`name`TEXT,
`price`REAL,
PRIMARY KEY(`id`)
);
";

        $r = $this->db->q->Expr($query)->execute($this->db->c);
        
    }

    protected function populateTable() {
        $queries = [
            "INSERT INTO `items` VALUES (1,'Candy',1.00);",
            "INSERT INTO `items` VALUES (2,'TShirt',5.34);",
            "INSERT INTO `items` VALUES (3,'LongSleeveT',5.34);"
        ];
        
        foreach ($queries as $query) {
            $this->db->initializeQuery();
            $r = $this->db->q->Expr($query)->execute($this->db->c);
        }
    }
}