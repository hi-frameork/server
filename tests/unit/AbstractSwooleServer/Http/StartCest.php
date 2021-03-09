<?php
namespace AbstractSwooleServer\Http;

use Stubs\SwooleHttpServer;
use UnitTester;

class StartCest
{
    protected $setting = [
        'swoole' => [
            'daemonize' => true,
        ]
    ];

    public function _before()
    {
        // $pid = pcntl_fork();
        // if ($pid) {
            // $server = new SwooleHttpServer($this->setting);
            // $server->start();
        // }
    }

    public function _after()
    {
        // $server = new SwooleHttpServer($this->setting);
        // $server->stop();
    }

    /**
     * 初始化测试（服务不启动
     */
    public function createServerInstance(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - createServerInstance()');

        $server = new SwooleHttpServer($this->setting);
        $I->assertTrue($server->isRunning());
    }

    /**
     * 以默认方式启动服务
     */
    // public function start(UnitTester $I)
    // {
        // // $I->wantToTest(SwooleHttpServer::class . ' - start()');

        // // $server = new SwooleHttpServer($this->setting);
        // // $server->start();

        // // $I->assertTrue($server->isRunning());

        // // $pid = file_get_contents($server->getPidFile());
        // // $I->assertEquals($pid, $server->getPid());
    // }

    // public function softStop(UnitTester $I)
    // {
        // // $I->wantToTest(SwooleHttpServer::class . ' - softStop()');

        // // $server = new SwooleHttpServer($this->setting);

        // // $I->assertTrue($server->isRunning());

        // // $pid = file_get_contents($server->getPidFile());
        // // $I->assertEquals($pid, $server->getPid());

        // // $server->stop();
    // }

    /**
     * 在指定的端口上启动服务
     */
    // public function startWithAppointPort(UnitTester $I)
    // {
    // }

    /**
     * 在指定 host 上启动服务
     */
    // public function startWithAppointHost(UnitTester $I)
    // {
    // }

    /**
     * 以指定配置启动服务
     */
    // public function startWithAppoinConfig(UnitTester $I)
    // {
    // }
}
