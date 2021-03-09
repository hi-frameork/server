<?php

declare(strict_types=1);

namespace AbstractServer;

use Hi\Server\AbstractServer;
use Stubs\Server;
use UnitTester;

class ConstructTestCest
{
    public function serverConstruct(UnitTester $I)
    {
        $I->wantToTest('\Stubs\Server - __construct()');

        $server = new Server();
        $class  = AbstractServer::class;
        $I->assertInstanceOf($class, $server);

        return $server;
    }
}

