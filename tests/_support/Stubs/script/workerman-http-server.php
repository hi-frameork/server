<?php

$autoload = strstr(__DIR__, 'tests', true) . 'vendor/autoload.php';
require $autoload;


use Hi\Server\AbstractWorkermanServer;
use Workerman\Worker;

class WorkermanHttpServer extends AbstractWorkermanServer
{
    protected $eventHandle = [
        'onMessage',
    ];

    public function onMessage($connection)
    {
        $connection->send('workerman');
    }

    protected function createServer()
    {
        $socketName = 'http://' . $this->host() . ':' . $this->port();

        Worker::$stdoutFile = '/dev/null';
        Worker::$pidFile = $this->pidFile();
        Worker::$logFile = $this->defaultLogPath();
        Worker::$daemonize = true;

        $server = new Worker($socketName);
        $server->name = $this->name();

        return $server;
    }
}

$server = new WorkermanHttpServer();
$server->start();
