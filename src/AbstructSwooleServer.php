<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructSwooleServer extends AbstructServer
{
    public function start(callable $handle, callable $taskHandle)
    {
        $swoole = $this->createServer();
    }

    public function restart()
    {
    }

    public function stop(bool $force = false)
    {
    }

    /**
     * 返回 swoole server 实例
     *
     * @return \Swoole\Server
     */
    abstract protected function createServer();
}

