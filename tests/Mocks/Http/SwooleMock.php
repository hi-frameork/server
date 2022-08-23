<?php

namespace Hi\Server\Tests\Mocks\Http;

use Hi\Server;
use Swoole\Http\Server as HttpServer;

class SwooleMock extends Server
{
    public static $defaultConfig = [
        'pid_file' => '/tmp/hi-test.pid',
        'log_file' => '/tmp/hi-test.log',
    ];

    public static function newInstance()
    {
        return new static(static::$defaultConfig);
    }

    /**
     * @inheritdoc
     */
    protected function createServer()
    {
        return new HttpServer($this->config->get('host'), $this->config->get('port'));
    }

    public function start(): void
    {
        $server = $this->createServer();
        $server->set(static::$defaultConfig);
        $server->on('request', function ($req, $res) {
            $res->end('hi-test');
        });
        $server->start();
    }
}
