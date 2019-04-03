<?php
namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; 
// this autoincludes classes in src/Wittlib

use PHPUnit\Framework\TestCase;
use Wittlib\Stats; //name of the class in src/Wittlib/Stats.php

class StatsTest extends TestCase {
    public function setUp () {
        // run this before every test
        $this->stats = new Stats(); 
        $data = array(0, 1, 1, 2, 3, 5, 8, 13, 21);
        $this->stats->loadData($data); 
    }

    public function testDataLoadedCorrectly () {
        //$this->assertEquals($expected_value, $actual_value);
        //$this is the StatsTest object

        //        $this->assertEquals('array',get_class($this->stats->data)); 
        $this->assertEquals(9, sizeof($this->stats->data)); 
        $this->assertEquals(0, $this->stats->data[0]);
        //        $this->assertIsInt($this->stats->data[3]);
    }
    /*
    public function testSomeOtherThing() {
    }
    */
}
