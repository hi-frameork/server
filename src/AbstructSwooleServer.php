<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructSwooleServer extends AbstructServer
{
    protected $swoole;

    abstract protected function createServer();

    public function start(): void
    {
        $swoole       = $this->createServer();
        $this->swoole = $swoole;

        $this->registerHandle();

        $swoole->start();
    }

    public function registerHandle()
    {
        $handles = get_class_methods($this);

        foreach ($handles as $value) {
            if ('on' == substr($value, 0, 2)) {
                $this->swoole->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }

        return $this;
    }
}

