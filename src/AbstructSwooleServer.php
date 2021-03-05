<?php declare(strict_types=1);

namespace Hi\Server;

use Swoole\Server;

abstract class AbstructSwooleServer extends AbstructServer
{
    /**
     * @var Server
     */
    protected $swoole;

    public function start(int $port = 9527, string $host = '127.0.0.1'): void
    {
        $this->processPort($port);
        $this->processHost($host);

        $this->swoole = $this->createServer();

        $this->registerHandle();
        $this->swoole->start();
    }

    protected function registerHandle()
    {
        $handles = get_class_methods($this);

        foreach ($handles as $value) {
            if ('on' == substr($value, 0, 2)) {
                $this->swoole->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }

        return $this;
    }

    /**
     * 返回 swoole server 实例
     *
     * @return \Swoole\Server
     */
    abstract protected function createServer();
}

