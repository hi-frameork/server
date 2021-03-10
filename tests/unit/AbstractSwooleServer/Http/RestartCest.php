<?php
namespace AbstractSwooleServer\Http;

use UnitTester;
use Stubs\SwooleHttpServer;

class RestartCest extends CommonCest
{
    public function testSoftRestart(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testSoftRestart()');

        $server = $this->startServer($this->setting);

        $olodPidTree = $server->servicePidTree($server->getPid());
        $server->restart();
        $newPidTree = $server->servicePidTree($server->getPid());

        $I->assertNotSame($olodPidTree, $newPidTree);
    }
}
