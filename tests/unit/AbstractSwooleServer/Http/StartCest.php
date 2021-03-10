<?php
namespace AbstractSwooleServer\Http;

use UnitTester;
use Stubs\SwooleHttpServer;

class StartCest extends CommonCest
{
    /**
     * 初始化测试（服务不启动
     */
    public function testRunningStatus(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testRunningStatus()');

        $server = $this->startServer($this->setting);
        $I->assertTrue($server->isRunning());
    }

    /**
     * 以默认方式启动服务
     */
    public function testCheckPid(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testCheckPid()');

        $server = $this->startServer($this->setting);
        $I->assertEquals(file_get_contents($server->getPidFile()), $server->getPid());
    }

    /**
     * 在指定的端口上启动服务
     */
    public function testStartWithAppointPort(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testStartWithAppointPort()');

        $port = 8000;
        $server = $this->startServer(array_merge($this->setting, ['port' => $port]));

        $I->assertSame($server->port(), $port);
    }

    /**
     * 在指定 host 上启动服务
     */
    public function testStartWithAppointHost(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testStartWithAppointHost()');

        $host = '0.0.0.0';
        $server = $this->startServer(array_merge($this->setting, ['host' => $host]));

        $I->assertSame($server->host(), $host);
    }

    /**
     * 以指定配置启动服务
     */
    // public function startWithAppoinConfig(UnitTester $I)
    // {
    // }
}
