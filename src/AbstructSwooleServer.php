<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructSwooleServer extends AbstructServer
{
    abstract protected function createServer();

    public function start(callable $handle, callable $taskHandle)
    {
    }

    public function restart()
    {
    }

    public function stop(bool $force = false)
    {
    }
}

