<?php
namespace Stubs;

use Hi\Server\AbstractServer;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Server extends AbstractServer
{
    public function start(int $port = 9527, string $host = '127.0.0.1')
    {
    }

    public function pidFile(): string
    {
        return '';
    }
}
