<?php
namespace AbstractSwooleServer\Http;

use Stubs\SwooleHttpServer;
use UnitTester;

class StopCest extends CommonCest
{
    // tests
    public function testForceStop(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testForceStop()');

        $server = $this->startServer($this->setting);
        $I->assertTrue($server->stop(true));
    }
}
