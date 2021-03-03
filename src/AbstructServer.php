<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructServer implements ServerInterface
{
    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var int
     */
    protected $port = 9527;

    /**
     * 返回当前库版本号
     */
    public function version(): string
    {
        return static::VERSION;
    }
}
