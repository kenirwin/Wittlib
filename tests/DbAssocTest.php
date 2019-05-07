<?php
namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use \atk4\dsql\Query;
use Wittlib\DbAssoc;

define ('DSN','sqlite::memory:');
define ('USER',null);
define ('PASS',null);

class DbAssocTest extends TestCase {

    public function setUp() {
        $this->db = new DbAssoc();
        $this->createTables();
        $this->initializeQuery(); //setup next query as $this->q
    }

    /* tests */

    
    public function testCreatesDatabaseConnection() {
       $this->assertStringStartsWith(
            'atk4\dsql\Query_',
            get_class($this->db->q)
        );
    }
 

    /* utility functions */

    public function tearDown() {
        unset($this->db->q);
    }


    private function initializeQuery() {
        $this->db->q = $this->db->c->dsql(); //new Query();
    }


    public function createTables() {
        $db_new_structure = "
CREATE TABLE `db_new` (
  `ID` smallint(6) NOT NULL,
  `title` mediumtext NOT NULL,
  `url` mediumtext NOT NULL,
  `route_to_db` int(11) DEFAULT NULL,
  `mobile_url` varchar(255) NOT NULL,
  `deprecated` date DEFAULT NULL,
  `cancelled` date DEFAULT NULL,
  `use_new_db` varchar(11) DEFAULT NULL,
  `see_ref` int(11) DEFAULT NULL,
  `see_also` int(11) DEFAULT NULL,
  `dates` varchar(25) DEFAULT NULL,
  `short_desc` varchar(120) DEFAULT NULL,
  `long_desc` mediumtext,
  `ft` char(1) NOT NULL DEFAULT 'N',
  `etext` char(1) NOT NULL DEFAULT 'N',
  `warnings` varchar(255) DEFAULT NULL,
  `suppress` char(1) NOT NULL DEFAULT 'N',
  `requireproxy` char(1) NOT NULL DEFAULT 'Y',
  `proxy_prohibited` char(1) DEFAULT NULL,
  `proxy_problem` char(1) NOT NULL DEFAULT 'N',
  `proxy_config` mediumtext,
  `provider` varchar(50) DEFAULT NULL,
  `funding` varchar(50) DEFAULT NULL,
  `subscribe_thru` varchar(50) DEFAULT NULL,
  `support_phone` varchar(50) DEFAULT NULL,
  `support_url` varchar(255) DEFAULT NULL,
  `total_annual_cost` decimal(10,0) DEFAULT NULL,
  `witt_annual_cost` decimal(10,0) DEFAULT NULL,
  `expires` date DEFAULT NULL,
  `subscription_no` varchar(255) DEFAULT NULL,
  `sub_notes` mediumtext,
  PRIMARY KEY (`ID`)
)";

        $subjects_structure = "
CREATE TABLE `subjects` (
  `subj_code` varchar(10) NOT NULL DEFAULT '',
  `subject` varchar(50) NOT NULL DEFAULT '',
  `liaison` varchar(15) DEFAULT NULL,
  `dept` varchar(50) NOT NULL DEFAULT '',
  `journ_only` char(1) NOT NULL DEFAULT 'N',
  `db_list` char(1) DEFAULT 'Y',
  `registrar_list` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`subj_code`)
)";

        $db_assoc_structure = "
CREATE TABLE `db_assoc` (
  `id` int(11) NOT NULL DEFAULT '0',
  `subj_code` varchar(50) NOT NULL DEFAULT '',
  `primacy` char(1) NOT NULL DEFAULT 'N'
)";

        $db_new_data = "INSERT INTO `db_new` (`ID`, `title`, `url`, `route_to_db`, `mobile_url`, `deprecated`, `cancelled`, `use_new_db`, `see_ref`, `see_also`, `dates`, `short_desc`, `long_desc`, `ft`, `etext`, `warnings`, `suppress`, `requireproxy`, `proxy_prohibited`, `proxy_problem`, `proxy_config`, `provider`, `funding`, `subscribe_thru`, `support_phone`, `support_url`, `total_annual_cost`, `witt_annual_cost`, `expires`, `subscription_no`, `sub_notes`) VALUES
(19,'Art Full Text (formerly Art Abstracts from H.W. Wilson)','http://search.ebscohost.com/login.asp?profile=web&defaultdb=aft',NULL,'',NULL,NULL,NULL,NULL,NULL,'1977-','Index & abstracts of art and art history articles','','Y','N','','N','Y',NULL,'N',NULL,'EBSCO','','OhioLINK','','',NULL,NULL,NULL,NULL,NULL),
(280,'Art Index Retrospective','http://search.ebscohost.com/login.asp?profile=web&defaultdb=air',NULL,'',NULL,NULL,NULL,NULL,NULL,'1921-1989','Index & abstracts of art and art history articles','','Y','N','','N','Y',NULL,'N',NULL,'EBSCO','','OhioLINK','','',NULL,NULL,NULL,NULL,NULL),
(21,'BasicBIOSIS','http://newfirstsearch.oclc.org/;autho=100105655;timeout=600;FSIP;dbname=BasicBIOSIS',NULL,'',NULL,'2011-08-01','30',NULL,NULL,'1994-','Index to biological science articles','','N','N','','Y','Y',NULL,'N',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(52,'EconLit','https://search.ebscohost.com/login.aspx?authtype=ip,uid&profile=ehost&defaultdb=ecn',NULL,'',NULL,NULL,NULL,NULL,NULL,'1969-','Index to articles in economics','','N','N','','N','Y',NULL,'N',NULL,'EBSCO','PTP','OhioLINK',NULL,NULL,NULL,NULL,NULL,NULL,NULL)";

        $subjects_data = "
INSERT INTO `subjects` (`subj_code`, `subject`, `liaison`, `dept`, `journ_only`, `db_list`, `registrar_list`) VALUES
('engl','English','kirwin','','N','Y','Y'),
('art','Art','kirwin','','N','Y','Y'),
('musi','Music','kirwin','','N','Y','Y'),
('thdn','Theatre & Dance','kirwin','','N','Y','Y'),
('comm','Communication','kirwin','','N','Y','Y'),
('acct','Accounting','','busn','N','Y','Y'),
('econ','Economics','petersk','','N','Y','Y'),
('lang','World Languages and Cultures','petersk','','N','Y','Y'),
('educ','Education','kirwin','','N','Y','Y'),
('reli','Religion','ssmailes','','N','Y','Y'),
('wmst','Womens Studies','ssmailes','','N','Y','Y'),
('phil','Philosophy','ssmailes','','N','Y','Y'),
('geol','Geology','amizikar','','N','Y','Y'),
('geog','Geography','amizikar','','N','Y','Y'),
('chem','Chemistry','amizikar','','N','Y','Y'),
('biol','Biology','amizikar','','N','Y','Y'),
('phys','Physics','amizikar','','N','Y','Y'),
('math','Mathematics','amizikar','','N','Y','Y'),
('comp','Computer Science','amizikar','','N','Y','Y'),
('psyc','Psychology','amizikar','','N','Y','Y'),
('global','International Studies','petersk','','N','Y','Y'),
('urban','Urban Studies','amizikar','','N','Y','Y'),
('soci','Sociology','petersk','','N','Y','Y'),
('poli','Political Science','petersk','','N','Y','Y'),
('eas','East Asian Studies','ssmailes','','N','Y','Y'),
('ras','Russian and Central Eurasian Studies','petersk','','N','Y','Y'),
('hist','History','dlehman','','N','Y','Y'),
('lis','Library & Information Studies','','','N','Y','N'),
('environ','Environmental Studies / Environmental Science','','biol','N','Y','Y'),
('film','Cinema Studies','','engl','N','Y','Y'),
('law','Law','','mgmt','N','Y','N'),
('leisure','Hobbies, Leisure & Recreation','','','Y','N','N'),
('social-wk','Social Work & Family Studies','','soci','N','Y','N'),
('anthro','Anthroplogy & Ethnography','','soci','N','Y','N'),
('engin','Engineering','','phys','N','Y','N'),
('health','Health Science & Medicine','amizikar','biol','N','Y','Y'),
('trade','Trade Journals','','','Y','N','N'),
('home_ec','Home Economics','','','Y','N','N'),
('archaeo','Archaeology','','art','N','Y','N'),
('astro','Astronomy','','phys','N','Y','Y'),
('military','Military','','poli','Y','N','N'),
('news','News','','poli','N','Y','N'),
('hfs','Health, Fitness & Sports','dlehman','','N','Y','Y'),
('non','Non-Journal Entries','','','Y','N','N'),
('amst','American Studies','dlehman','','N','Y','Y'),
('afst','African, Diaspora, and International Studies','dlehman','','N','Y','Y'),
('gensci','General Science','','','Y','Y','N'),
('general','General Interest','','','N','Y','N'),
('gender','Gender & Sexuality','','soci','N','Y','N'),
('honors','Honors','petersk','','N','N','Y'),
('interdisc','Interdisciplinary Studies','','','Y','N','Y'),
('sced','School of Graduate and Professional Studies','dlehman','','N','N','N'),
('wittsem','Wittenberg Seminar','petersk','','N','N','Y'),
('stats_db','Statistical Databases','','','N','Y','N'),
('busn','Business','petersk','','N','Y','Y'),
('bmb','Biochemistry / Molecular Biology','amizikar','','N','Y','Y'),
('lbst','Liberal Studies',NULL,'','N','N','Y'),
('marine','Marine Sciences','amizikar','','N','N','N'),
('neur','Neuroscience','amizikar','','N','N','Y'),
('past','Pre-Modern & Ancient Studies','ssmailes','','N','N','Y'),
('nur','Nursing','amizikar','','N','Y','Y'),
('fren','French',NULL,'lang','N','N','Y')
";

        $db_assoc_data = "
INSERT INTO `db_assoc` (`id`, `subj_code`, `primacy`) VALUES
(19,'art','Y'),
(52,'econ','Y')
";

        $this->executeQuery($db_new_structure);
        $this->executeQuery($db_new_data);
        $this->executeQuery($subjects_structure);
        $this->executeQuery($subjects_data);
        $this->executeQuery($db_assoc_structure);
        $this->executeQuery($db_assoc_data);
    }

    public function executeQuery($query) {
        $this->initializeQuery();
        $this->db->q->Expr($query)->execute($this->db->c);
    }

}

?>
