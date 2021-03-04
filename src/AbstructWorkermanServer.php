<?php declare(strict_types=1);

namespace Hi\Server;

use Workerman\Worker;

abstract class AbstructWorkermanServer extends AbstructServer
{
    /**
     * @var string
     */
    protected $socketName;

    public function start(): void
    {
        $this->socketName      = "http://{$this->host}:{$this->port}";
        $worker                = new Worker($this->socketName);
        $worker->onWorkerStart = [$this, 'onWorkerStart'];
        $worker->onMessage     = [$this, 'onMessage'];
        Worker::runAll();
    }

    public function onWorkerStart()
    {
        echo "Workerman http server is started at {$this->socketName}\n";
    }
}
