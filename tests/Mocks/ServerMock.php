<?php

namespace Hi\Server\Tests\Mocks;

use Hi\Server\Server as ServerServer;

class ServerMock extends ServerServer
{
    public function getEventHanle()
    {
        $this->collectionEventHandle();
        return $this->eventHandle;
    }

    public function start(): void
    {
    }

    public function onRequest()
    {
    }

    public function onShutdown()
    {
    }

    public function onTask()
    {
    }
}
