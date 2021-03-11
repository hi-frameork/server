<?php
namespace AbstractSwooleServer\Http;

use UnitTester;
use Stubs\SwooleHttpServer;

class RestartCest extends CommonCest
{
    public function testReload(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testReload()');

        $server = $this->startServer($this->setting);

        $olodPidTree = $server->servicePids();
        $server->reload();
        $newPidTree = $server->servicePids();

        $I->assertNotSame($olodPidTree, $newPidTree);
    }
}
