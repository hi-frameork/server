<?php

namespace Hi\Server;

abstract class Server
{
    use ServerTrait;

    /**
     * Server config
     *
     * @var Config
     */
    protected $config;

    /**
     * Server 回调事件集合
     * (也可用于查看已注册事件列表)
     *
     * @var array<string, array<string, string>>
     */
    protected $eventHandle = [];

    /**
     * Construct
     *
     * @param array{} $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * 返回 Config 配置对象
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * 启动服务
     */
    abstract public function start(): void;

    /**
     * 平滑重启服务
     */
    public function reload(): void
    {
        if ($this->isRunning()) {
            posix_kill($this->pid(), SIGUSR1);
        }
    }

    /**
     * 平滑停止服务
     */
    public function stop(): void
    {
        if ($this->isRunning()) {
            posix_kill($this->pid(), SIGTERM);
            $this->waitForStop();
        }
    }

    /**
     * 强制停止服务(强制杀掉服务相关所有进程)
     */
    public function shutdown(): void
    {
        $pids = $this->childPids();
        if (! $pids) {
            return;
        }

        // 携带 Master 进程 PID
        array_unshift($pids, $this->pid());

        // 执行 kill -9 强制结束进程
        exec('kill -9 ' . implode(' ', $pids));

        $this->waitForStop();
    }
}

