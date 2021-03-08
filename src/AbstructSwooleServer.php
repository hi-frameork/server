<?php declare(strict_types=1);

namespace Hi\Server;

use Swoole\Server;

abstract class AbstructSwooleServer extends AbstructServer implements ServerInterface
{
    /**
     * @var Server
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

        $this->swoole = $this->createServer();

        $this->registerEventHandle();
        $this->swoole->start();
    }

    protected function registerEventHandle()
    {
        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }
    }

    /**
     * 返回 swoole server 实例
     *
     * @return \Swoole\Server
     */
    abstract protected function createServer();
}

