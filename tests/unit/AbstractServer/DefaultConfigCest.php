<?php
namespace AbstractServer;

use Stubs\Server;
use UnitTester;

class DefaultConfigCest
{
    protected function createServer()
    {
        return new Server();
    }

    // tests
    public function testGetConfig(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getConfig()');

        $server = new Server();

        $expected = [
            'host'      => null,
            'port'      => null,
            'name'      => null,
            'pid_file'  => null,
            'log_file'  => null,
            'swoole'    => [],
            'workerman' => [],
        ];

        $I->assertSame($expected, $server->config());
    }

    public function testGetHost(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getHost()');

        $I->assertEquals('127.0.0.1', $this->createServer()->host());
    }

    public function testGetPort(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getPort()');

        $I->assertEquals(9527, $this->createServer()->port());
    }

    public function testGetName(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - getName()');

        $I->assertEquals('hi-server', $this->createServer()->name());
    }

    public function testGetDefaultRunDirectory(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - defaultRunDirectory()');

        $I->assertEquals(
            getcwd() . '/../',
            $this->createServer()->defaultRunDirectory()
        );
    }

    public function testGetDefaultLogPath(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - defaultLogPath()');

        $server = $this->createServer();

        $I->assertEquals(
            $server->defaultRunDirectory() . $server->name() . '.log',
            $server->defaultLogPath()
        );
    }

    public function testGetDefaultPidFilePath(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - defaultPidFilePath()');

        $server = $this->createServer();

        $I->assertEquals(
            $server->defaultRunDirectory() . $server->name() . '.pid',
            $server->defaultPidFilePath()
        );
    }
}
