<?php

declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructServer implements ServerInterface
{
    /**
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * @var int
     */
    protected $port = 8000;

    /**
     * {@inheritDoc}
     */
    public function version(): string
    {
        return static::VERSION;
    }
}
