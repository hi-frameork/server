<?php declare(strict_types=1);

namespace Hi\Server;

use Workerman\Worker;

abstract class AbstructWorkermanServer extends AbstructServer implements ServerInterface
{
    /**
     * @var Worker
     */
    protected $server;

    /**
     * @var array
     */
    protected $eventHandle = [];

    public function start(int $port = 9527, string $host = '127.0.0.1'): void
    {
        $this->processPort($port);
        $this->processHost($host);

        $this->server = $this->createServer();

        $this->registerEventHandle();
        Worker::runAll();
    }

    protected function registerEventHandle()
    {
        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->{$value} = [$this, $value];
            }
        }
    }

    abstract protected function createServer();
}
