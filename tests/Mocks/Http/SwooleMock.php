<?php

namespace Hi\Server\Tests\Mocks\Http;

use Hi\Server\SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleHttpServer;

class SwooleMock extends SwooleServer
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
        return new SwooleHttpServer(
            $this->config->getHost(),
            $this->config->getPort()
        );
    }

    public function onRequest(Request $request, Response $response)
    {
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->end('Hello Swoole');
    }
}
