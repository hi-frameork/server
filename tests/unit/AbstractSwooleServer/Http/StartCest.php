<?php
namespace AbstractSwooleServer\Http;

use RuntimeException;
use Stubs\SwooleHttpServer;
use UnitTester;

class StartCest
{
    protected $setting = [
        'swoole' => [
            'daemonize' => true,
        ]
    ];

    private function startServer()
    {
        $server = new SwooleHttpServer($this->setting);

        if ($server->isRunning()) {
            throw new RuntimeException('服务启动失败');
        }
        
        $script = strstr(__DIR__, 'tests', true) . 'tests/_support/Stubs/script/swoole-http-server.php';
        $data   = str_replace('"', '\"', json_encode($this->setting));
        $data   = str_replace(',', '\,', $data);
        $cmd    = "php {$script} -arg={$data}";
        // echo $cmd, PHP_EOL;
        exec($cmd, $output, $return);

        if ($return !== 0) {
            echo "\n\nError output: ", PHP_EOL;
            array_map(function ($line) { echo $line . "\n"; }, $output);
            exit($return);
        }

        // 等待 server 启动再进行后续业务
        $count = 1;
        do {
            if ($count > 10000) {
                throw new RuntimeException('操作失败，未检测到服务成功启动');
            }
            if ($server->isRunning()) {
                break;
            }
            $count++;
            usleep(50);
        } while (true);

        return $server;
    }

    public function _after()
    {
        $server = new SwooleHttpServer($this->setting);
        $server->stop();
    }

    /**
     * 初始化测试（服务不启动
     */
    public function testRunningStatus(UnitTester $I)
    {
        $server = $this->startServer();

        $I->wantToTest(SwooleHttpServer::class . ' - testRunningStatus()');
        $I->assertTrue($server->isRunning());
    }

    /**
     * 以默认方式启动服务
     */
    public function testCheckPid(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testCheckPid()');

        $server = $this->startServer();

        $I->assertEquals(
            file_get_contents($server->getPidFile()),
            $server->getPid()
        );
    }

    // public function testForceStop(UnitTester $I)
    // {
        // $I->wantToTest(SwooleHttpServer::class . ' - testForceStop()');

        // $server = $this->startServer();

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
    public function testStartWithAppointPort(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testStartWithAppointPort()');

        $port = 8000;

        $this->setting['port'] = $port;

        $server = $this->startServer();

        $I->assertSame($server->port(), $port);
    }

    /**
     * 在指定 host 上启动服务
     */
    public function testStartWithAppointHost(UnitTester $I)
    {
        $I->wantToTest(SwooleHttpServer::class . ' - testStartWithAppointHost()');

        $host = '0.0.0.0';

        $this->setting['host'] = $host;

        $server = $this->startServer();

        $I->assertSame($server->host(), $host);
        sleep(10);
    }

    /**
     * 以指定配置启动服务
     */
    // public function startWithAppoinConfig(UnitTester $I)
    // {
    // }
}
