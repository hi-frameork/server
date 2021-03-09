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

    public function restart(bool $force = false)
    {
    }

    public function stop(bool $force = false)
    {
    }

    protected function registerEventHandle()
    {
        if (empty($this->eventHandle)) {
            throw new InvalidArgumentException('无法启动服务，必须在 eventHandle 中设置事件');
        }

        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->{$value} = [$this, $value];
            }
        }
    }

    abstract protected function createServer();
}
