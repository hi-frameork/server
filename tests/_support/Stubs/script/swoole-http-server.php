<?php

$autoload = strstr(__DIR__, 'tests', true) . 'vendor/autoload.php';
require $autoload;


use Hi\Server\AbstractSwooleServer;
use Swoole\Http\Server;

class SwooleHttpServer extends AbstractSwooleServer
{
    protected $eventHandle = [
        'onRequest',
        'onStart',
    ];

    public function onRequest()
    {
    }

    public function onStart()
    {
    }

    protected function createServer()
    {
        return new Server($this->host(), $this->port());
    }
}


$setting = explode('=', $argv[1])[1] ?? null;
if (! $setting) {
    exit(1);
}
$setting = json_decode($setting, true);

$server = new SwooleHttpServer($setting);
$server->start();

