<?php

namespace Hi\Server\Tests;

use Hi\Server\Tests\Mocks\Http\SwooleMock;
use PHPUnit\Framework\TestCase;

class HttpServerTest extends TestCase
{
    protected $procHandle;

    protected $host = '0.0.0.0';

    protected $port = 9527;

    /**
     * 启动 Swoole HTTP 服务
     */
    protected function setUp(): void
    {
        $cliScript = __DIR__ . '/Mocks/Http/swoole_start_cli.php';
        $descriptorspec = [0 => STDIN, 1 => STDOUT, 2 => STDERR,];
        $cmd = "exec php ${cliScript} 2>/dev/null";
        $this->procHandle = proc_open($cmd, $descriptorspec, $pipes, __DIR__);

        $i = 0;
        while (($i++ < 30) && !($fp = @fsockopen($this->host, $this->port))) {
            usleep(500000);
        }

        if ($fp) {
            fclose($fp);
        }
    }

    /**
     * 停止 Swoole HTTP 服务
     */
    protected function tearDown(): void
    {
        if ($this->procHandle) {
            proc_terminate($this->procHandle);
        }
    }

    /**
     * 测试已默认方式启动 Swoole HTTP Server
     */
    public function testStartSwooleServer()
    {
        $rest = file_get_contents(sprintf('http://%s:%d', $this->host, $this->port));
        $this->assertSame('hi-test', $rest);
    }

    public function testGetPid()
    {
        $server = SwooleMock::newInstance();
        $this->assertEquals(
            file_get_contents($server->getConfig()->get('pid_file')),
            $server->getPid()
        );
    }

    public function testGetChildPids()
    {
        $this->assertIsArray(SwooleMock::newInstance()->getChildPids());
    }

    public function testStop()
    {
        $server = SwooleMock::newInstance();
        $server->stop();
        $this->assertFalse($server->isRunning());
    }
}
