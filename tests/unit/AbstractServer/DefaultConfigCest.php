<?php
namespace AbstractServer;

use Stubs\Server;
use UnitTester;

class DefaultConfigCest
{
    // tests
    public function testGetConfig(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getConfig()');

        $server = new Server();

        $expected = [
            'host'      => null,
            'port'      => null,
            'name'      => null,
            'swoole'    => [],
            'workerman' => [],
        ];

        $I->assertEquals($expected, $server->config());
    }

    public function testGetHost(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getHost()');

        $server = new Server();
        $I->assertEquals('127.0.0.1', $server->host());
    }

    public function testGetPort(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getPort()');

        $server = new Server();
        $I->assertEquals(9527, $server->port());
    }

    public function testGetName(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getName()');

        $server = new Server();
        $I->assertEquals('hi-server', $server->name());
    }

    public function testGetDefaultRunDirectory(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - defaultRunDirectory()');

        $server = new Server();
        $I->assertEquals(
            getcwd() . '/../',
            $server->defaultRunDirectory()
        );
    }

    public function testGetDefaultLogPath(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - defaultLogPath()');

        $server = new Server();
        $I->assertEquals(
            $server->defaultRunDirectory() . $server->name() . '.log',
            $server->defaultLogPath()
        );
    }

    public function testGetDefaultPidFilePath(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - defaultPidFilePath()');

        $server = new Server();
        $I->assertEquals(
            $server->defaultRunDirectory() . $server->name() . '.pid',
            $server->defaultPidFilePath()
        );
    }
}
