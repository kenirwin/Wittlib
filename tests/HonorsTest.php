<?php

namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use \atk4\dsql\Query;
use Wittlib\Honors;

define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'config.php';
//getProxyConfig();

class ImportHonorsTest extends TestCase {
    public function setUp() {
        $this->db = new Honors;
        $this->createTable();
        $this->populateTable();
        $this->initializeQuery(); //setup next query
    }

    public function testTestTableHasThreeRows() {
        $results = $this->db->q->table('senior_theses')->get();
        $this->assertEquals(4, sizeof($results));
    }

    
    public function testGetsRecordById() {
        $data = $this->db->getRecord(1); // no id, get first untried
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('nicole_robinson_03.pdf', $data['filename']);
    }

    public function testGetListByAuthor() {
        $data = $this->db->getListByAuthor();
        $this->assertEquals(49, $data[0]['id']); //first author is 49=Fetherolf
    }

    public function testGetListByYear() {
        $getByYear = true;
        $data = $this->db->getListByAuthor($getByYear);
        $this->assertEquals(164, $data[0]['id']); //most recent au 164=Glass
    }

    public function testGetListByYearAndDept() {
        $getByYear = true;
        $dept = 'Philosophy';
        $data = $this->db->getListByAuthor($getByYear,$dept);
        $this->assertEquals(49, $data[0]['id']); //most philos = 49=Fetherolf
    }

    public function testListsDepts() {
        $data = $this->db->getDepts();
        $expected = ['Communication','History','Philosophy','Religion'];
        $this->assertEquals($expected, array_keys($data));
    }
    /*
    public function testGetListByDept() {
        $data = $this->db->getListBy('dept');
        $this->assertEquals(1, $data[0]['id']); //first author is 1=History
    }
    */

    
    /*
    public function testGetsDbRowById() {
        $data = $this->db->getRow(4); // no id, get first untried
        $this->assertEquals(4, $data[0]['id']);
        $this->assertEquals('meghan_biniker_03.pdf', $data[0]['filename']);
    }

    public function testGetCorrectWorldFilepath() {
        $path = $this->db->getFilepath(1);
        $this->assertEquals('/docs/lib/witt_pubs/honors/world/nicole_robinson_03.pdf', $path);
    }

    public function testGetCorrectCampusiFlepath() {
        $path = $this->db->getFilepath(4);
        $this->assertEquals('/docs/lib/witt_pubs/honors/campus/meghan_biniker_03.pdf', $path);
    }
    
    public function testFileIsReadable() {
        $path = $this->db->getFilepath(1);
        $this->assertIsReadable($path);
    }

    public function testCanGetContents() {
        $contents = $this->db->getContents(1);
        $this->assertTrue(strlen($contents) > 1000);
    }

    public function testAddFileToDb() {
        $this->db->insertFile(1);
        //        $this->assertEquals('',$result);
        $data = $this->db->getRow(1);
        $this->assertRegExp('/^%PDF/', $data[0]['file_pdf']);
    }
    */
    public function tearDown() {
        unset($this->db);
    }

    private function initializeQuery() {
        $this->db->q = $this->db->c->dsql(); //new Query();
    }

    public function createTable() {
        $this->initializeQuery();
        $query = "
CREATE TABLE IF NOT EXISTS `senior_theses` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `alt_filename` varchar(255) DEFAULT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `added_authors` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `alt_title` varchar(255) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `dept1` varchar(100) NOT NULL,
  `dept2` varchar(100) DEFAULT NULL,
  `year` int(11) NOT NULL DEFAULT '2018',
  `advisor` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `perm` varchar(10) NOT NULL DEFAULT 'all',
  `abstract` text,
  `published` text,
  `pass` varchar(50) NOT NULL DEFAULT 'pass',
  `univ_honors` char(1) DEFAULT 'Y',
  `honors` char(1) NOT NULL DEFAULT 'Y',
  `suppress` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`)
); 
";
        $this->db->q->Expr($query)->execute($this->db->c);
    }

    protected function populateTable() {

        $queries = [
            "INSERT INTO `senior_theses` VALUES (1, 'nicole_robinson_03.pdf', NULL, 'Nicole A.', 'Robinson', NULL, '\"Here''s Tae Us! Wha''s Like Us?\" Jacobitism and the Creation of a Scottish National Identity', NULL, 86, 'History', '', 2003, 'Tammy Proctor', 's03.nrobinson@wittenberg.edu', 'all', 'The Jacobite Risings that took place during the eighteenth century were meant to restore the deposed Stuarts to the throne of Great Britain. They were never \r\nsuccessful in that goal, but the Risings were instrumental in a larger cultural change that took place during the eighteenth and nineteenth centuries. Throughout history, there had been a distinct cultural divide \r\nbetween the Highlands and the Lowlands of Scotland; Lowlanders viewed Highlanders as barbaric, uncultured, and savage. Today, however, customs of the Highlands, such as kilts, bagpipes, and Highland ballads, express what it means to be Scottish. After the Jacobite Risings, the actual Highland way of life was first destroyed and then embraced by the Lowlands as a way to express Scottish national identity. The roots of this drastic shift are deeply \r\nimbedded in Jacobitism, and the effects of the change are seen in the way in which both Scots and the outside world view Scotland today.', '', 'distinction', 'Y', 'Y', 'N');",
            "INSERT INTO `senior_theses` VALUES (49, 'christina_fetherolf_08.pdf', NULL, 'Christina', 'Fetherolf', NULL, 'To Marry God: The Portrayal of God as an Abusive Husband in Ezekiel', NULL, 112, 'Religion', 'Philosophy', 2008, 'Barbara Keiser;Warren Copeland', 'christina.fetherolf@gmail.com', 'campus', '\"To Marry God\" offers a close reading of Ezekiel 16 in its historical, social and literary context in order to understand and evaluate the depiction of God as abusive husband.  The author''s main argument is that Ezekiel''s use of the marriage metaphor cannot be understood outside of the blessings and curses of Deuteronomy.  Background on the historical situation is provided, along with a discussion of the marriage metaphor''s use within the canon.  The paper ends with an attempt to bring the issue to modern-day relevance.', NULL, 'pass', 'Y', 'Y', 'N')",
            "INSERT INTO `senior_theses` VALUES (17, 'e_a_guhde_05.pdf', NULL,  'E. A.', 'Guhde', NULL, 'This is a made up title', NULL, NULL, 'Philosophy', NULL, 2005, 'McHugh, Nancy', 's05.eguhde@wittenberg.edu', 'none', 'Made up abstract', NULL, 'pass', 'Y', 'Y', 'Y');",
            "INSERT INTO `senior_theses` VALUES (164, 'stephanie_glass_2017.pdf', NULL, 'Stephanie', 'Glass', NULL, 'Not Just Fun and Games: Feminism in <cite>Parks and Recreation</cite>', NULL, 47, 'Communication', NULL, 2017, 'Sheryl Cunningham;Catherine Waggoner;Nancy McHugh', 'glasshalffull140@gmail.com', 'all', 'In this study, elements of narrative and their influence on viewers of <i>Parks and Recreation</i> are analyzed in connection to the promotion of social change for gender equality. The situational comedy narrative presents feminism as a matter which influences a community, reflected within the public sphere, and within government. Comedy as a discourse has the potential to promote powerful social movements by bringing issues to light and exposing new dimensions of these issues to general audiences. Two questions guided the study: 1) How do narrative elements in Parks and Recreation reflect feminism? and 2) How do audiences respond to these narrative elements in terms of attitudes about gender and social change?  The episodes for this study, \"Women in Garbage\" and \"Article Two,\" were selected because they contain narratives that explicitly deal with women''s rights in a public matter, and focus on the implementation of institutional changes to law and public policy. Participants in this study determined <i>Parks and Recreation</i> to be a feminist text which uses humor to reinforce the feminist ideals encoded within the text. Parks and Recreation does not only impact human understanding, but has the ability to impact human action and interaction and promote social change for gender equality.', NULL, 'pass', 'Y', 'Y', 'N')"

        ];
        
        foreach ($queries as $query) {
            $this->initializeQuery();
            $r = $this->db->q->Expr($query)->execute($this->db->c);
        }
    }
}
