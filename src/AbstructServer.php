<?php

declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructServer implements InterfaceServer
{
    abstract protected function createServer();
}
