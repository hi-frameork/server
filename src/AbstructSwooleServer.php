<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructSwooleServer extends AbstructServer
{
    abstract protected function createServer();
}

