<?php declare(strict_types=1);

namespace Hi\Server;

use Swoole\Server;

/**
 * Swoole 运行容器基类
 */
abstract class AbstructSwooleServer extends AbstructServer implements ServerInterface
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var string
     */
    protected $pidFile;

    /**
     * @var array
     */
    protected $eventHandle = [];

    /**
     * 启动 swoole 服务
     */
    public function start(int $port = 9527, string $host = '127.0.0.1'): void
    {
        $this->processPort($port);
        $this->processHost($host);

        $this->server = $this->createServer();

        $this->registerEventHandle();
        $this->server->set($this->processSetting());
        $this->server->start();
    }

    public function restart(bool $force = false)
    {
    }

    public function stop(bool $force = false)
    {
    }

    protected function registerEventHandle()
    {
        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }
    }

    protected function processSetting(): array
    {
        $setting = array_merge($this->defaultSetting(), $this->config['swoole'] ?? []);
        return $setting;
    }

    protected function defaultSetting()
    {
        return [
            'pid_file'          => '/tmp/hi-server.pid',
            'log_file'          => '/tmp/hi.log',
            'worker_num'        => 2,
            'task_worker_num'   => 2,
            'task_tmpdir'       => '/tmp',
            'open_cpu_affinity' => true,
        ];
    }

    /**
     * 由子类实现，返回对应 swoole server 实例
     * 
     * 对于不同的实现子类，返回对应 server 实例，例如：
     *  在 http 服务中，返回 \Swoole\Http\Server
     *  在 tcp 服务中，返回 \Swoole\Tcp\Server
     *
     * @return \Swoole\Server
     */
    abstract protected function createServer();
}

