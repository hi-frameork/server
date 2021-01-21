<?php

declare(strict_types=1);

namespace Hi\Server;

interface InterfaceServer
{
    /**
     * 在指定端口与地址启动监听
     * @return void
     */
    public function listen(int $port = 8000, string $host = '127.0.0.1');
}
