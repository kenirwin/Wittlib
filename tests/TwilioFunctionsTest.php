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
    
    public function testCarrierLookupFindsCarrier() {
        print 'Test commented out so as not to incur Twilio fees. Uncomment if you need to test Twilio functionality'.PHP_EOL;
        /*
        $ken = '+19377279713';
        $carrier = $this->fn->carrierLookup($ken);
        $this->assertRegExp('/verizon/i', $carrier);
        */
    }
}

