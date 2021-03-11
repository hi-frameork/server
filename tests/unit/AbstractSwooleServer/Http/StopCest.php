<?php
namespace AbstractSwooleServer\Http;

use Stubs\SwooleHttpServer;
use UnitTester;

class StopCest extends CommonCest
{
    // tests
    public function testStop(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testStop()');

        $server = $this->startServer($this->setting);

        $I->assertTrue($server->stop());
    }

    public function testShutdown(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testShutdown()');

        $server = $this->startServer($this->setting);

        $I->assertTrue($server->shutdown());
    }
}
