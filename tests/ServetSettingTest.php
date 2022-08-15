<?php

namespace Hi\Server;

use Hi\Server\Tests\Mocks\Http\SwooleMock;
use Hi\Server\Tests\Mocks\ServerMock;
use PHPUnit\Framework\TestCase;

class ServerSettingTest extends TestCase
{
    /**
     * 测试注册服务响应回调事件
     */
    public function testCollectionEventHandle()
    {
        $server = new ServerMock();
        $this->assertSame(
            [
                'request' => [ServerMock::class, 'onRequest'],
                'shutdown' => [ServerMock::class, 'onShutdown'],
                'task' => [ServerMock::class, 'onTask'],
            ],
            $server->getEventHanle()
        );
    }

    /**
     * 测试 Swoole HTTP Server 设置
     */
    public function testSetting()
    {
        $server = new SwooleMock(['swoole' => [
            'pid_file'   => '/tmp/hi-test.pid',
            'log_file'   => '/tmp/hi-test.log',
            'stats_file' => '/tmp/stats_file',
        ]]);

        $this->assertEquals(
            [
                'pid_file'   => '/tmp/hi-test.pid',
                'log_file'   => '/tmp/hi-test.log',
                'stats_file' => '/tmp/stats_file',
                'open_cpu_affinity' => true,
            ],
            $server->setting()
        );
    }
}
