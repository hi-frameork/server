<?php declare(strict_types=1);

namespace Stubs;

use Hi\Server\AbstractWorkermanServer;
use Workerman\Worker;

class WorkermanHttpServer extends AbstractWorkermanServer
{
    protected $eventHandle = [
        'onMessage',
    ];

    protected function createServer()
    {
        return new Worker('http://' . $this->host() . ':' . $this->port());
    }

    public function onMessage($connection)
    {
        $connection->send('workerman');
    }
}
