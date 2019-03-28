<?php

namespace Wittlib\Test;

require dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use Wittlib\TwilioFunctions;
include ('/docs/lib/remote/config.php');
getConfig();

class TwilioFunctionsTest extends TestCase {
    public function setUp() {
        $this->fn = new TwilioFunctions(TWILIO_SID,TWILIO_TOKEN,TWILIO_FROM);
    }

        /*
    public function testSendSms() {
        $success = $this->fn->send(TWILIO_REAL_RECIPIENT, 'Test message from function: '. __FUNCTION__ . PHP_EOL . 'File: '.__FILE__ . PHP_EOL . 'Line: '. __LINE__);
        $this->assertTrue($success);
    }


    public function testCarrierLookupFindsCarrier() {
        $ken = '+19377279713';
        $carrier = $this->fn->carrierLookup($ken);
        $this->assertRegExp('/verizon/i', $carrier);
    }
        */

    public function testMessage() {
        print '***************'.PHP_EOL;
        print 'Tests commented out so as not to incur Twilio fees.'.PHP_EOL.'Uncomment if you need to test Twilio functionality'.PHP_EOL;
        print '***************'.PHP_EOL;
        $this->assertTrue(true);
    }
    

    public function tearDown () {
        unset ($this->fn);
    }
}


